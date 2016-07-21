<?php namespace Samuell\ContentEditor\Components;

use File;
use Str;
use BackendAuth;
use Response;
use Input;

use Cms\Classes\Content;
use Cms\Classes\CmsObject;
use Cms\Classes\MediaLibrary;
use Cms\Classes\ComponentBase;

use Samuell\ContentEditor\Models\Settings;

class ContentEditor extends ComponentBase
{
    public $content;
    public $file;
    public $buttons;

    public function componentDetails()
    {
        return [
            'name'        => 'Content Editor',
            'description' => 'Edit your front-end content in page.'
        ];
    }

    public function defineProperties()
    {
        return [
            'file' => [
                'title'       => 'Content file',
                'description' => 'Content block filename to edit, optional',
                'default'     => '',
                'type'        => 'dropdown',
            ]
        ];
    }

    public function getFileOptions()
    {
        return Content::sortBy('baseFileName')->lists('baseFileName', 'fileName');
    }

    public function onRun()
    {
        if ($this->checkEditor()) {

            $this->buttons = Settings::get('enabled_buttons');

            // put content tools js + css
            $this->addCss('assets/content-tools.min.css');
            $this->addJs('assets/content-tools.min.js');
            $this->addJs('assets/contenteditor.js');
        }
    }

    public function onRender()
    {

        $this->file = $this->property('file');

        // Compatability with RainLab.Translate
        if (class_exists('\RainLab\Translate\Classes\Translator')){
            $locale = \RainLab\Translate\Classes\Translator::instance()->getLocale();
            $fileName = substr_replace($this->file, '.'.$locale, strrpos($this->file, '.'), 0);
            if (($content = Content::loadCached($this->page->controller->getTheme(), $fileName)) !== null)
                $this->file = $fileName;
        }

        if ($this->checkEditor()){
            if (Content::load($this->getTheme(), $this->file))
                $this->content = $this->renderContent($this->file);
        } else {
            return $this->renderContent($this->file);
        }
    }

    public function onSave()
    {
        if ($this->checkEditor()){

            $fileName = post('file');

            if ($load = Content::load($this->getTheme(), $fileName)) {
                $fileContent = $load; // load existed content file
            }else{
                $fileContent = Content::inTheme($this->getTheme()); // create new content file if not exists
            }

            $fileContent->fill([
                'fileName' => $fileName,
                'markup' => post('content')
            ]);
            $fileContent->save();

        }
    }

    public function onUploadImage()
    {
        if ($this->checkEditor()) {

            try {
                 if (!Input::hasFile('file')) {
                     return;
                 }
                 $uploadedFile = Input::file('file');
                 $fileName = $uploadedFile->getClientOriginalName();

                 // Convert uppcare case file extensions to lower case
                 $extension = strtolower($uploadedFile->getClientOriginalExtension());
                 $fileName = File::name($fileName).'.'.$extension;

                 // File name contains non-latin characters, attempt to slug the value
                 if (!$this->validateFileName($fileName)) {
                     $fileNameSlug = Str::slug(File::name($fileName), '-');
                     $fileName = $fileNameSlug.'.'.$extension;
                 }
                 if (!$uploadedFile->isValid()) {
                     throw new ApplicationException($uploadedFile->getErrorMessage());
                 }

                 $path = '/contenteditor/';
                 $path = MediaLibrary::validatePath($path);

                 MediaLibrary::instance()->put(
                     $path.'/'.$fileName,
                     File::get($uploadedFile->getRealPath())
                 );

                 return Response::json([
                     "url"  => '/storage/app/media/'.$path.'/'.$fileName,
                     "size" => ''
                 ]);
             }
             catch (Exception $ex) {
                 return $ex;
             }

        }
    }

    public function onSaveImage()
    {
        if ($this->checkEditor()) {

            list($width, $height) = getimagesize(post('url'));

            return Response::json([
                'url'   => post('url'),
                'width' => post('width'),
                'crop'  => post('crop'),
                'alt'   => "Image",
                'size'  => [
                            'width' => $width,
                            'height' => $height
                            ]
            ]);
        }
    }

    public function checkEditor()
    {
        $backendUser = BackendAuth::getUser();
        return $backendUser && $backendUser->hasAccess(Settings::get('permissions', 'cms.manage_content'));
    }
}
