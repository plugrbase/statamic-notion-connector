<?php

use Illuminate\Support\Facades\Route;
use Plugrbase\StatamicNotionConnector\Http\Controllers\NotionMappingController;

Route::name('notion-mapping.')->prefix('notion-mapping')->group(function () {
    Route::get('/', [NotionMappingController::class, 'index'])->name('index');
    Route::get('create', [NotionMappingController::class, 'create'])->name('create');
    Route::post('/', [NotionMappingController::class, 'store'])->name('store');
    Route::get('/{mapping}', [NotionMappingController::class, 'show'])->name('view');
    Route::get('/{mapping}/edit', [NotionMappingController::class, 'edit'])->name('edit');
    Route::put('/{mapping}', [NotionMappingController::class, 'update'])->name('update');
    Route::delete('/{mapping}', [NotionMappingController::class, 'destroy'])->name('destroy');
    
    // Add this route for importing entries
    Route::post('database/{databaseId}/import', [NotionMappingController::class, 'importEntry'])->name('import-entry');
}); 