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
    
    // Laporan
    Route::delete('laporan/{id}/delete', [\App\Http\Controllers\LaporanController::class, 'deleteLaporan'])->name('laporan.delete');
    
    // Kertas Kerja
    Route::get('kertas-kerja/generate/{st_id}', [App\Http\Controllers\KertasKerjaController::class, 'generate'])->name('kertas-kerja.generate');
    Route::get('kertas-kerja/fetch-reference', [App\Http\Controllers\KertasKerjaController::class, 'fetchReference'])->name('kertas-kerja.fetch-reference');
    Route::resource('kertas-kerja', App\Http\Controllers\KertasKerjaController::class);
    Route::post('/kertas-kerja/{id}/submit', [App\Http\Controllers\KertasKerjaController::class, 'submit'])->name('kertas-kerja.submit');
    Route::post('/kertas-kerja/{id}/approve', [App\Http\Controllers\KertasKerjaController::class, 'approve'])->name('kertas-kerja.approve');
    Route::post('/kertas-kerja/{id}/reject', [App\Http\Controllers\KertasKerjaController::class, 'reject'])->name('kertas-kerja.reject');
    Route::get('/kertas-kerja/{id}/review-sheet', [App\Http\Controllers\KertasKerjaController::class, 'reviewSheet'])->name('kertas-kerja.review-sheet');
    
    // QA Rendal
    Route::get('/kertas-kerja/{id}/qa', [App\Http\Controllers\KertasKerjaController::class, 'qa'])->name('kertas-kerja.qa');
    Route::post('/kertas-kerja/update-qa-single', [App\Http\Controllers\KertasKerjaController::class, 'updateQaSingle'])->name('kertas-kerja.update-qa-single');
    Route::post('/kertas-kerja/update-tanggapan-qa', [\App\Http\Controllers\KertasKerjaController::class, 'updateTanggapanQa'])->name('kertas-kerja.update-tanggapan-qa');
    Route::post('/kertas-kerja/{id}/finalize-qa', [\App\Http\Controllers\KertasKerjaController::class, 'finalizeQa'])->name('kertas-kerja.finalize-qa');
    
    // Laporan
    Route::get('laporan', [App\Http\Controllers\LaporanController::class, 'index'])->name('laporan.index');
    Route::post('laporan/{id}/upload', [App\Http\Controllers\LaporanController::class, 'uploadLaporan'])->name('laporan.upload');
    
    // QA Actions
    Route::post('kertas-kerja/{id}/unfinalize', [App\Http\Controllers\KertasKerjaController::class, 'unfinalizeQa'])->name('kertas-kerja.unfinalize-qa');
    Route::post('kertas-kerja/{id}/unfinalize-approval', [App\Http\Controllers\KertasKerjaController::class, 'unfinalizeApproval'])->name('kertas-kerja.unfinalize-approval');
    Route::get('kertas-kerja/{id}/print', [App\Http\Controllers\LaporanController::class, 'printKertasKerja'])->name('kertas-kerja.print');

    // Program Kerja
    Route::resource('program-kerja', App\Http\Controllers\ProgramKerjaController::class);
    Route::post('program-kerja/assign', [App\Http\Controllers\ProgramKerjaController::class, 'assignLangkah'])->name('program-kerja.assign');
    Route::post('program-kerja/remove-assignment', [App\Http\Controllers\ProgramKerjaController::class, 'removeAssignment'])->name('program-kerja.remove-assignment');
    Route::post('program-kerja/langkah/{id}/status', [App\Http\Controllers\ProgramKerjaController::class, 'updateStatus'])->name('program-kerja.update-status');
    Route::post('program-kerja/langkah/{id}/link-kk', [App\Http\Controllers\ProgramKerjaController::class, 'linkKertasKerja'])->name('program-kerja.link-kk');
    Route::get('program-kerja/{id}/print', [App\Http\Controllers\ProgramKerjaController::class, 'print'])->name('program-kerja.print');

    // Users
    Route::resource('users', App\Http\Controllers\UserController::class);
    
    // Perwakilan
    Route::resource('perwakilan', App\Http\Controllers\PerwakilanController::class);
    
    // Master Data
    Route::resource('roles', App\Http\Controllers\RoleController::class);
    
    Route::get('templates/{template}/builder', [App\Http\Controllers\TemplateController::class, 'builder'])->name('templates.builder');
    Route::get('templates/{template}/preview', [App\Http\Controllers\TemplateController::class, 'preview'])->name('templates.preview');
    Route::post('templates/{template}/clone', [App\Http\Controllers\TemplateController::class, 'cloneTemplate'])->name('templates.clone');
    Route::get('templates/{template}/bobot-summary', [App\Http\Controllers\TemplateController::class, 'bobotSummary'])->name('templates.bobot-summary');
    Route::post('templates/{template}/indicators', [App\Http\Controllers\TemplateController::class, 'storeIndicator'])->name('templates.indicators.store');
    Route::put('indicators/{indicator}', [App\Http\Controllers\TemplateController::class, 'updateIndicator'])->name('templates.indicators.update');
    Route::delete('indicators/{indicator}', [App\Http\Controllers\TemplateController::class, 'destroyIndicator'])->name('templates.indicators.destroy');
    Route::post('indicators/{indicator}/criteria', [App\Http\Controllers\TemplateController::class, 'storeCriteria'])->name('templates.criteria.store');
    Route::put('criteria/{criteria}', [App\Http\Controllers\TemplateController::class, 'updateCriteria'])->name('templates.criteria.update');
    Route::delete('criteria/{criteria}', [App\Http\Controllers\TemplateController::class, 'destroyCriteria'])->name('templates.criteria.destroy');
    
    Route::resource('templates', App\Http\Controllers\TemplateController::class);

    // Template Program Kerja (Rendal)
    Route::resource('template-pka', App\Http\Controllers\TemplatePKAController::class);
    Route::post('template-pka/{id}/langkah', [App\Http\Controllers\TemplatePKAController::class, 'storeLangkah'])->name('template-pka.langkah.store');
    Route::put('template-pka/langkah/{id}', [App\Http\Controllers\TemplatePKAController::class, 'updateLangkah'])->name('template-pka.langkah.update');
    Route::delete('template-pka/langkah/{id}', [App\Http\Controllers\TemplatePKAController::class, 'destroyLangkah'])->name('template-pka.langkah.destroy');

    // Profile
    Route::get('profile', [App\Http\Controllers\UserController::class, 'profile'])->name('profile');

    // Notifications
    Route::get('notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return redirect($notification->data['url'] ?? route('home'));
    })->name('notifications.read');
});
