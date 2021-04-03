<?php namespace Samuell\ContentEditor\Http\Controllers;

use File;
use Input;
use Response;
use Exception;
use ApplicationException;
use Cms\Classes\MediaLibrary;
use Illuminate\Routing\Controller;
use October\Rain\Database\Attach\Resizer;
use Samuell\ContentEditor\Models\Settings;
use October\Rain\Filesystem\Definitions as FileDefinitions;

/**
 * ImageController
 * 
 * Handle content editor image upload
 */
class ImageController extends Controller
{
    public function upload()
    {
        try {
            if (!Input::hasFile('image')) {
                throw new ApplicationException('File missing from request');
            }

            $uploadedFile = Input::file('image');
            $fileName = $uploadedFile->getClientOriginalName();

            /*
             * Convert uppcare case file extensions to lower case
             */
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            $fileName = File::name($fileName).'.'.$extension;

            /*
             * File name contains non-latin characters, attempt to slug the value
             */
            if (!$this->validateFileName($fileName)) {
                $fileNameClean = $this->cleanFileName(File::name($fileName));
                $fileName = $fileNameClean . '.' . $extension;
            }

            if (!$uploadedFile->isValid()) {
                throw new ApplicationException($uploadedFile->getErrorMessage());
            }

            if (!$this->validateFileType($fileName)) {
                throw new ApplicationException(Lang::get('backend::lang.media.type_blocked'));
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

    /**
     * Check for blocked / unsafe file extensions
     *
     * @param string
     * @return bool
     */
    protected function validateFileType($name)
    {
        $extension = strtolower(File::extension($name));

        $allowedFileTypes = FileDefinitions::get('imageExtensions');

        if (!in_array($extension, $allowedFileTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Validate a proposed media item file name.
     *
     * @param string
     * @return bool
     */
    protected function validateFileName($name)
    {
        if (!preg_match('/^[\w@\.\s_\-]+$/iu', $name)) {
            return false;
        }

        if (strpos($name, '..') !== false) {
            return false;
        }

        return true;
    }
}
