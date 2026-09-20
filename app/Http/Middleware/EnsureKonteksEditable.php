<?php

namespace App\Http\Middleware;

use App\Contracts\HasLayananContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan konteks bisa diedit oleh operator yang bersangkutan.
 *
 * - Admin: selalu bisa akses
 * - Operator: hanya jika konteks milik layanan yang mereka buat (created_by)
 *
 * Route harus memiliki parameter {konteks} (route model binding).
 */
class EnsureKonteksEditable
{
    public function handle(Request $request, Closure $next, string $modelClass = \App\Models\MrKonteks::class): Response
    {
        $konteks = $request->route('konteks');

        // Route model binding: jika string, cari manual
        if (! $konteks instanceof HasLayananContext) {
            $konteks = $modelClass::findOrFail($konteks);
        }

        $user = $request->user();

        // Admin selalu bisa akses
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Operator hanya boleh akses konteks milik layanannya sendiri
        // isEditableByOperator() selalu true (tidak ada approval), cukup cek kepemilikan
        if ($konteks->layanan && $konteks->layanan->created_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen perangkat daerah lain.');
        }

        return $next($request);
    }
}
