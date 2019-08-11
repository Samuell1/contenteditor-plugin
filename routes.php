<?php

Route::group(['prefix' => 'contenteditor'], function () {
    Route::post('image/upload', 'Samuell\ContentEditor\Http\Controllers\ImageController@upload');
    Route::post('image/save', 'Samuell\ContentEditor\Http\Controllers\ImageController@save');
});