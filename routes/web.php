<?php

use App\Http\Controllers\LogoutController;
use App\Livewire\Admin\ReviewIndex;
use App\Livewire\Admin\TrashIndex;
use App\Livewire\Admin\User\UserIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use App\Livewire\Konteks\KonteksForm;
use App\Livewire\Konteks\KonteksIndex;
use App\Livewire\Mpn\Konteks\KonteksMpnIndex;
use App\Livewire\Mpn\Konteks\KonteksMpnForm;
use App\Livewire\Mpn\IndikatorCapaian\IndikatorCapaianForm;
use App\Livewire\Mpn\Pengetahuan\PengetahuanIndex;
use App\Livewire\Mpn\Pengetahuan\PengetahuanForm;
use App\Livewire\Mpn\Pengumpulan\PengumpulanIndex;
use App\Livewire\Mpn\Pengumpulan\PengumpulanForm;
use App\Livewire\Mpn\Pemanfaatan\PemanfaatanForm;
use App\Livewire\Mpn\AlihPengetahuan\AlihPengetahuanForm;
use App\Livewire\Pemantauan\PemantauanForm;
use App\Livewire\Risiko\PetaRisiko;
use App\Livewire\Risiko\RisikoForm;
use App\Livewire\Risiko\RisikoIndex;
use App\Livewire\Sasaran\SasaranForm;
use App\Livewire\StrukturPelaksana\StrukturPelaksanaForm;
use Illuminate\Support\Facades\Route;

// ─── Guest Routes ─────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// ─── Authenticated Routes ─────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Dashboard Utama: Daftar Layanan
    Route::get('/', \App\Livewire\Layanan\LayananIndex::class)->name('layanan.index');
    Route::get('/layanan', \App\Livewire\Layanan\LayananIndex::class)->name('layanan.index_alias');
    
    Route::get('/layanan/baru', \App\Livewire\Layanan\LayananForm::class)->name('layanan.create');
    Route::get('/layanan/{layanan}/edit', \App\Livewire\Layanan\LayananForm::class)->name('layanan.edit');
    
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    Route::middleware('has.layanan')->group(function () {
        // Dashboard Hub (Launcher 5 Modul per Layanan)
        Route::get('/layanan/{layanan}/manajemen', Dashboard::class)->name('layanan.dashboard');

        // Modul Manajemen Risiko (Konteks & Formulir)
        Route::prefix('manajemen-risiko')->group(function () {
            Route::get('/', KonteksIndex::class)->name('konteks.index');

            Route::prefix('konteks/{konteks}')->middleware('konteks.access')->group(function () {
                Route::get('/', KonteksForm::class)->name('konteks.form');
                Route::get('/sasaran', SasaranForm::class)->name('sasaran.form');
                Route::get('/struktur', StrukturPelaksanaForm::class)->name('struktur.form');
                Route::get('/risiko', RisikoIndex::class)->name('risiko.index');
                Route::get('/layanan-digital', \App\Livewire\LayananDigital\LayananDigitalIndex::class)->name('layanan-digital.index');
                Route::get('/risiko/{risiko}', RisikoForm::class)->name('risiko.form');
                Route::get('/peta-risiko', PetaRisiko::class)->name('risiko.peta');
                Route::get('/pemantauan', PemantauanForm::class)->name('pemantauan.form');
            });
        });
        // Modul Manajemen Pengetahuan
        Route::prefix('manajemen-pengetahuan')->group(function () {
            Route::get('/', KonteksMpnIndex::class)->name('konteks-mpn.index');

            Route::prefix('konteks/{konteks}')
                ->middleware('konteks.access:App\Models\MpnKonteks')
                ->group(function () {
                    Route::get('/', KonteksMpnForm::class)->name('konteks-mpn.form');
                    Route::get('/indikator-capaian', IndikatorCapaianForm::class)->name('mpn.indikator-capaian.form');
                    
                    Route::get('/pengetahuan', PengetahuanIndex::class)->name('mpn.pengetahuan.index');
                    Route::get('/pengetahuan/{pengetahuan}', PengetahuanForm::class)->name('mpn.pengetahuan.form');
                    
                    Route::get('/pengetahuan/{pengetahuan}/pengumpulan', PengumpulanIndex::class)->name('mpn.pengumpulan.index');
                    Route::get('/pengetahuan/{pengetahuan}/pengumpulan/{pengumpulan}', PengumpulanForm::class)->name('mpn.pengumpulan.form');
                    
                    Route::get('/pengetahuan/{pengetahuan}/pengumpulan/{pengumpulan}/pemanfaatan', PemanfaatanForm::class)->name('mpn.pemanfaatan.form');
                    Route::get('/pengetahuan/{pengetahuan}/pengumpulan/{pengumpulan}/alih-pengetahuan', AlihPengetahuanForm::class)->name('mpn.alih-pengetahuan.form');
                });
        });
    });

    // ─── Admin Only Routes ────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/review', ReviewIndex::class)->name('review.index');
        Route::get('/review/layanan/{layanan}', KonteksIndex::class)->name('review.konteks');
        Route::get('/review/mpn/layanan/{layanan}', KonteksMpnIndex::class)->name('review.mpn.konteks');
        Route::get('/user', UserIndex::class)->name('user.index');
        Route::get('/trash', TrashIndex::class)->name('trash.index');
    });
});
