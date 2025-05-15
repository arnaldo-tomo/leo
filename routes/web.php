<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorizedPersonController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\DashboardController;
// Route::get('/', function () {
//     return view('welcome');
// });



Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Rotas para pessoas autorizadas
Route::prefix('authorized')->name('authorized.')->group(function () {
    Route::get('/', [AuthorizedPersonController::class, 'index'])->name('index');
    Route::get('/create', [AuthorizedPersonController::class, 'create'])->name('create');
    Route::post('/', [AuthorizedPersonController::class, 'store'])->name('store');
    Route::get('/{authorizedPerson}/edit', [AuthorizedPersonController::class, 'edit'])->name('edit');
    Route::put('/{authorizedPerson}', [AuthorizedPersonController::class, 'update'])->name('update');
    Route::delete('/{authorizedPerson}', [AuthorizedPersonController::class, 'destroy'])->name('destroy');
    Route::get('/get-all', [AuthorizedPersonController::class, 'getAll'])->name('get-all');
});

// Rotas para monitoramento de acesso// Rotas para monitoramento de acesso
Route::prefix('access')->name('access.')->group(function () {
    Route::get('/monitor', [AccessController::class, 'monitor'])->name('monitor');
    Route::get('/logs', [AccessController::class, 'logs'])->name('logs');
    Route::post('/record', [AccessController::class, 'recordAccess'])->name('record');
    Route::get('/recent-logs', [AccessController::class, 'getRecentLogs'])->name('recent-logs');

    // Adicionar a rota de exportação que estava faltando
    Route::get('/export', [AccessController::class, 'export'])->name('export');
});
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
