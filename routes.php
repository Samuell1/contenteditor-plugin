<?php

use Samuell\ContentEditor\Models\Settings;
use Cms\Classes\MediaLibrary;
use Cms\Helpers\File as FileHelper;
use October\Rain\Database\Attach\Resizer;

Route::post('/samuell/contenteditor/image/upload', function () {

    if (checkEditor()) {

        try {
             if (!Input::hasFile('image')) {
                 throw new ApplicationException('File missing from request');
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

             $realPath = empty(trim($uploadedFile->getRealPath()))
                ? $uploadedFile->getPath() . DIRECTORY_SEPARATOR . $uploadedFile->getFileName()
                : $uploadedFile->getRealPath();

             MediaLibrary::instance()->put(
                 $path.'/'.$fileName,
                 File::get($realPath)
             );

             list($width, $height) = getimagesize($uploadedFile);

             return Response::json([
                 'url'      => MediaLibrary::instance()->getPathUrl($path.'/'.$fileName),
                 'filePath' => $path.'/'.$fileName,
                 'filename' => $fileName,
                 'size'     => [
                    $width,
                    $height
                ]
             ]);
         } catch (Exception $ex) {
             return $ex;
         }

    }

})->middleware('web');

Route::post('/samuell/contenteditor/image/save', function () {

    if (checkEditor()) {

        if (post('crop')) {
            $crop = explode(',', post('crop'));

            $file = MediaLibrary::instance()->get(post('filePath'));
            $tempDirectory = temp_path().'/contenteditor';
            $tempFilePath = temp_path().post('filePath');
            File::makeDirectory($tempDirectory, 0777, true, true);

            if (!File::put($tempFilePath, $file)) {
                throw new SystemException('Error saving remote file to a temporary location.');
            }

            Resizer::open($tempFilePath)
                ->crop(
                    floor(post('width') * $crop[1]),
                    floor(post('height') * $crop[0]),
                    floor(post('width') * $crop[3] - post('width') * $crop[1]),
                    floor(post('height') * $crop[2] - post('height') * $crop[0])
                )
                ->save($tempFilePath, 100);

            MediaLibrary::instance()->put(post('filePath'), file_get_contents($tempFilePath));
        }

        return Response::json([
            'url'   => post('url'),
            'width' => post('width'),
            'crop'  => post('crop'),
            'alt'   => post('alt'),
            'size'  => [
                post('width'),
                post('height')
            ]
        ]);

    }

})->middleware('web');

function checkEditor()
{
    $backendUser = BackendAuth::getUser();
    return $backendUser && $backendUser->hasAccess(Settings::get('permissions', 'cms.manage_content'));
}
