<?php

use Samuell\ContentEditor\Models\Settings;
use Cms\Classes\ComponentPartial;
use Cms\Classes\MediaLibrary;
use Cms\Helpers\File as FileHelper;

function checkEditor()
{
    $backendUser = BackendAuth::getUser();
    return $backendUser && $backendUser->hasAccess(Settings::get('permissions', 'cms.manage_content'));
}

Route::post('/samuell/contenteditor/image/upload', function () {

    if (checkEditor()) {

        try {
             if (!Input::hasFile('image')) {
                 return;
             }
             $uploadedFile = Input::file('image');
             $fileName = $uploadedFile->getClientOriginalName();

             // Convert uppcare case file extensions to lower case
             $extension = strtolower($uploadedFile->getClientOriginalExtension());
             $fileName = File::name($fileName).'.'.$extension;

             // File name contains non-latin characters, attempt to slug the value
             if (!FileHelper::validateName($fileName)) {
                 $fileNameSlug = Str::slug(File::name($fileName), '-');
                 $fileName = $fileNameSlug.'.'.$extension;
             }
             if (!$uploadedFile->isValid()) {
                 throw new ApplicationException($uploadedFile->getErrorMessage());
             }

             $path = Settings::get('image_folder', 'contenteditor');
             $path = MediaLibrary::validatePath($path);

             MediaLibrary::instance()->put(
                 $path.'/'.$fileName,
                 File::get($uploadedFile->getRealPath())
             );

             return Response::json([
                 'url'  => '/storage/app/media'.$path.'/'.$fileName,
                 'filename' => $fileNameSlug,
                 'size' => '600'
             ]);
         }
         catch (Exception $ex) {
             return $ex;
         }

    }

});

Route::post('/samuell/contenteditor/image/save', function () {

    if (checkEditor()) {

        //list($width, $height) = getimagesize(post('url'));

        return Response::json([
            'url'   => post('url'),
            'width' => post('width'),
            'crop'  => post('crop'),
            'alt'   => "Image",
            'size'  => [
                        'width' => '200',
                        'height' => '200'
                        ]
        ]);

    }

});
