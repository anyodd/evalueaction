<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    // Surat Tugas
    Route::get('surat-tugas/{surat_tuga}/print', [App\Http\Controllers\SuratTugasController::class, 'print'])->name('surat-tugas.print');
    Route::resource('surat-tugas', App\Http\Controllers\SuratTugasController::class);
    
    // Kertas Kerja
    Route::get('kertas-kerja/generate/{st_id}', [App\Http\Controllers\KertasKerjaController::class, 'generate'])->name('kertas-kerja.generate');
    Route::get('kertas-kerja/fetch-reference', [App\Http\Controllers\KertasKerjaController::class, 'fetchReference'])->name('kertas-kerja.fetch-reference');
    Route::resource('kertas-kerja', App\Http\Controllers\KertasKerjaController::class);
    Route::post('/kertas-kerja/{id}/submit', [App\Http\Controllers\KertasKerjaController::class, 'submit'])->name('kertas-kerja.submit');
    Route::post('/kertas-kerja/{id}/approve', [App\Http\Controllers\KertasKerjaController::class, 'approve'])->name('kertas-kerja.approve');
    Route::post('/kertas-kerja/{id}/reject', [App\Http\Controllers\KertasKerjaController::class, 'reject'])->name('kertas-kerja.reject');
    Route::post('/kertas-kerja/update-single', [App\Http\Controllers\KertasKerjaController::class, 'updateSingle'])->name('kertas-kerja.update-single');
    
    // Review
    Route::get('review', [App\Http\Controllers\ReviewController::class, 'index'])->name('review.index');
    
    // Laporan
    Route::get('laporan', [App\Http\Controllers\LaporanController::class, 'index'])->name('laporan.index');
    
    // Users
    Route::resource('users', App\Http\Controllers\UserController::class);
    
    // Perwakilan
    Route::resource('perwakilan', App\Http\Controllers\PerwakilanController::class);
    
    // Master Data
    Route::resource('roles', App\Http\Controllers\RoleController::class);
    
    Route::get('templates/{template}/builder', [App\Http\Controllers\TemplateController::class, 'builder'])->name('templates.builder');
    Route::post('templates/{template}/indicators', [App\Http\Controllers\TemplateController::class, 'storeIndicator'])->name('templates.indicators.store');
    Route::delete('indicators/{indicator}', [App\Http\Controllers\TemplateController::class, 'destroyIndicator'])->name('templates.indicators.destroy');
    Route::post('indicators/{indicator}/criteria', [App\Http\Controllers\TemplateController::class, 'storeCriteria'])->name('templates.criteria.store');
    Route::delete('criteria/{criteria}', [App\Http\Controllers\TemplateController::class, 'destroyCriteria'])->name('templates.criteria.destroy');
    
    Route::resource('templates', App\Http\Controllers\TemplateController::class);

    // Profile
    Route::get('profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
});
