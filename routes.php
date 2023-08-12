<?php

use Samuell\ContentEditor\Http\Controllers\AdditionalStylesController;
use Samuell\ContentEditor\Http\Controllers\ImageController;
use Samuell\ContentEditor\Http\Middleware\EditorPermissionsMiddleware;

Route::group(['prefix' => 'contenteditor'], function () {

    Route::middleware(['web', EditorPermissionsMiddleware::class])
        ->group(function () {
            Route::post('image/upload', [ImageController::class, 'upload']);
            Route::post('image/save', [ImageController::class, 'save']);
        });

    // Additional styles route
    Route::get('styles', [AdditionalStylesController::class, 'render']);
});
