<?php

Route::group(['prefix' => 'contenteditor'], function () {
    Route::post('image/upload', 'Samuell\ContentEditor\Http\Controllers\ImageController@upload');
    Route::post('image/save', 'Samuell\ContentEditor\Http\Controllers\ImageController@save');

    // Additional styles route
    Route::get('styles', 'Samuell\ContentEditor\Http\Controllers\AdditionalStylesController@render');
});
