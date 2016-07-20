<?php namespace Samuell\ContentEditor\Components;

use File;
use BackendAuth;
use Cms\Classes\Content;
use Cms\Classes\CmsObject;
use Cms\Classes\ComponentBase;

use Samuell\ContentEditor\Models\Settings;

class ContentEditor extends ComponentBase
{
    public $content;
    public $file;

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
            $this->page["editorButtons"] = Settings::get('enabled_buttons');

            $this->addCss('assets/content-tools.min.css');
            $this->addJs('assets/content-tools.min.js');
            $this->addJs('assets/contenteditor.js');
        }
    }
    public function onRender()
    {

        $this->file = $this->property('file');

        /*
         * Compatability with RainLab.Translate
         */
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
    public function checkEditor()
    {
        $backendUser = BackendAuth::getUser();
        return $backendUser && $backendUser->hasAccess(Settings::get('permissions', 'cms.manage_content'));
    }
}
