<?php namespace Samuell\ContentEditor\Http\Controllers;

use ApplicationException;
use Exception;
use Response;
use File;
use Input;
use Illuminate\Routing\Controller;
use October\Rain\Database\Attach\Resizer;
use Cms\Classes\MediaLibrary;
use Cms\Helpers\File as FileHelper;
use Samuell\ContentEditor\Models\Settings;
use Samuell\ContentEditor\Http\Middleware\EditorPermissionsMiddleware;
use October\Rain\Support\Facades\Str;

class ImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('web');
        $this->middleware(EditorPermissionsMiddleware::class);
    }

    public function upload()
    {
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
            throw new ApplicationException($ex);
        }

    }

    public function save()
    {
        $url = post('url');
        $crop = post('crop');
        $width = post('width');
        $height = post('height');
        $filePath = post('filePath');
        $relativeFilePath = config('cms.storage.media.path').$filePath;

        if ($crop && $crop != '0,0,1,1') {
            $crop = explode(',', $crop);

            $file = MediaLibrary::instance()->get(post('filePath'));
            $tempDirectory = temp_path().'/contenteditor';
            $tempFilePath = temp_path().post('filePath');
            File::makeDirectory($tempDirectory, 0777, true, true);

            if (!File::put($tempFilePath, $file)) {
                throw new SystemException('Error saving remote file to a temporary location.');
            }

            $width = floor(post('width') * $crop[3] - post('width') * $crop[1]);
            $height = floor(post('height') * $crop[2] - post('height') * $crop[0]);

            Resizer::open($tempFilePath)
                ->crop(
                    floor(post('width') * $crop[1]),
                    floor(post('height') * $crop[0]),
                    $width,
                    $height
                )
                ->save($tempFilePath, 100);

            $pathParts = pathinfo(post('filePath'));
            $newFilePath = $pathParts['dirname'] . '/' . $pathParts['filename'] . '-c.' . $pathParts['extension'];

            MediaLibrary::instance()->put($newFilePath, file_get_contents($tempFilePath));

            $url = MediaLibrary::instance()->getPathUrl($newFilePath);
        }

        return Response::json([
            'url'       => $url,
            'filePath'  => $relativeFilePath,
            'width'     => $width,
            'crop'      => post('crop'),
            'alt'       => post('alt'),
            'size'      => [
                $width,
                $height
            ]
        ]);
    }
}
