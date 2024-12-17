<?php

use Illuminate\Support\Facades\Route;
use Bento\BentoStatamic\Http\Controllers\ConfigController;
use Bento\BentoStatamic\Http\Controllers\AdvancedSettingsController;

Route::prefix('bento')->group(function () {
    Route::get('/', [ConfigController::class, 'index'])->name('bento.index');
    Route::post('/update', [ConfigController::class, 'update'])->name('bento.update');
    Route::post('/update-email', [ConfigController::class, 'updateEmail'])->name('bento.update-email');
    Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('bento.test-email');

    // Advanced Settings Routes
    Route::get('/advanced', [AdvancedSettingsController::class, 'index'])->name('bento.advanced');
    Route::post('/advanced/update', [AdvancedSettingsController::class, 'update'])->name('bento.advanced.update');
});
