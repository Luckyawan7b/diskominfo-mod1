<?php

namespace App\Providers;

use App\Models\MrKolomTambahan;
use App\Models\MrRisiko;
use App\Observers\MrKolomTambahanObserver;
use App\Observers\MrRisikoObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        MrRisiko::observe(MrRisikoObserver::class);
        MrKolomTambahan::observe(MrKolomTambahanObserver::class);
        \App\Models\MpnPengetahuan::observe(\App\Observers\MpnPengetahuanObserver::class);
        \App\Models\MpnPemanfaatan::observe(\App\Observers\MpnPemanfaatanObserver::class);
    }
}
