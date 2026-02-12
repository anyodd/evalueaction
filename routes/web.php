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
    Route::resource('kertas-kerja', App\Http\Controllers\KertasKerjaController::class);
    
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

    // Profile
    Route::get('profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');
});
