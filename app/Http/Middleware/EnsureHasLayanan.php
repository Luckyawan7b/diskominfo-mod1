<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasLayanan
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Hanya terapkan untuk operator
        if ($user && $user->isOperator()) {
            // Abaikan pengecekan jika sedang berada di route create/store layanan atau logout
            $ignoredRoutes = [
                'layanan.create',
                'livewire.update', // Untuk mengizinkan form Livewire submit
                'logout',
            ];

            if (!in_array($request->route()->getName(), $ignoredRoutes)) {
                $hasLayanan = \App\Models\Layanan::where('created_by', $user->id)->exists();

                if (!$hasLayanan) {
                    session()->flash('warning', 'Anda harus mengisi deskripsi layanan terlebih dahulu sebelum dapat mengakses halaman lain.');
                    return redirect()->route('layanan.create');
                }
            }
        }

        return $next($request);
    }
}
