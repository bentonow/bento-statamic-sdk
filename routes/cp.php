<?php

use Illuminate\Support\Facades\Route;
use Bento\BentoStatamic\Http\Controllers\ConfigController;
use Bento\BentoStatamic\Http\Controllers\AdvancedSettingsController;
use Bento\BentoStatamic\Http\Controllers\EventsController;

Route::prefix('bento')->group(function () {
    Route::get('/', [ConfigController::class, 'index'])->name('bento.index');
    Route::post('/update', [ConfigController::class, 'update'])->name('bento.update');
    Route::post('/update-email', [ConfigController::class, 'updateEmail'])->name('bento.update-email');
    Route::post('/test-email', [ConfigController::class, 'testEmail'])->name('bento.test-email');

    // Advanced Settings Routes
    Route::get('/advanced', [AdvancedSettingsController::class, 'index'])->name('bento.advanced');
    Route::post('/advanced/update', [AdvancedSettingsController::class, 'update'])->name('bento.advanced.update');

    // Events routes
    Route::get('/events', [EventsController::class, 'index'])->name('bento.events.index');
    Route::post('/events', [EventsController::class, 'store'])->name('bento.events.store');
    Route::get('/forms', [EventsController::class, 'getForms'])->name('bento.forms.index');
    Route::post('/forms/{handle}/event', [EventsController::class, 'updateFormEvent'])->name('bento.forms.update-event');
    Route::delete('/events/{event}', [EventsController::class, 'destroy'])->name('bento.events.destroy');
    // Tags
    Route::get('/tags', [EventsController::class, 'getTags'])->name('bento.tags.index');
    Route::get('/sync-tags', [EventsController::class, 'getSyncTags'])->name('bento.sync-tags.index');
    Route::post('/sync-tags', [EventsController::class, 'addSyncTag'])->name('bento.sync-tags.store');
    Route::delete('/sync-tags', [EventsController::class, 'removeSyncTag'])->name('bento.sync-tags.destroy');
});
