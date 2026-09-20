<?php

namespace App\Http\Middleware;

use App\Contracts\HasLayananContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKonteksAccessible
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modelClass = \App\Models\MrKonteks::class): Response
    {
        $konteks = $request->route('konteks');

        if (! $konteks) {
            return $next($request);
        }

        // Pastikan model sudah di-resolve (route model binding) atau lookup manual via model class
        if (! $konteks instanceof HasLayananContext) {
            $konteks = $modelClass::with('layanan')->findOrFail($konteks);
        }

        $user = $request->user();

        // Admin selalu lolos
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // Operator hanya lolos jika konteks milik layanan yang mereka buat
        if ($user && $user->isOperator()) {
            $layanan = $konteks->layanan;
            if ($layanan && $layanan->created_by === $user->id) {
                return $next($request);
            }
        }

        abort(403, 'Anda tidak memiliki akses ke dokumen perangkat daerah lain.');
    }
}
