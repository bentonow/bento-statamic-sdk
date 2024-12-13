<?php

use Illuminate\Support\Facades\Route;
use Bento\BentoStatamic\Http\Controllers\ConfigController;

Route::prefix('bento')->group(function () {
    Route::get('/', [ConfigController::class, 'index'])->name('bento.index');
    Route::post('/update', [ConfigController::class, 'update'])->name('bento.update');
    Route::post('/update-email', [ConfigController::class, 'updateEmail'])->name('bento.update-email');
    Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('bento.test-email');
});
