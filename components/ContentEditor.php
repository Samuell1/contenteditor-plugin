<?php namespace Samuell\ContentEditor\Components;

use File;
use BackendAuth;
use Cms\Classes\Content;
use Cms\Classes\ComponentBase;

class ContentEditor extends ComponentBase
{
    public $content;
    public $isEditor;
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
        $this->isEditor = $this->checkEditor();
        if ($this->isEditor) {
            $this->addCss('assets/content-tools.min.css');
            $this->addJs('assets/content-tools.min.js');
            $this->addJs('assets/contenteditor.js');
        }
    }
    public function onRender()
    {
        $this->file = $this->property('file');

        var_dump(File::extension($this->file));

        /*
         * Compatability with RainLab.Translate
         */
        if (class_exists('\RainLab\Translate\Classes\Translator')){
            $locale = \RainLab\Translate\Classes\Translator::instance()->getLocale();
            $fileName = substr_replace($this->file, '.'.$locale, strrpos($this->file, '.'), 0);
            if (($content = Content::loadCached($this->page->controller->getTheme(), $fileName)) !== null)
                $this->file = $fileName;
        }

        if ($this->isEditor){
            if (file_exists($this->getTheme()->getPath().$this->file))
                $this->content = $this->renderContent($this->file);
        } else {
            return $this->renderContent($this->file);
        }
    }
    public function onSave()
    {
        if ($this->checkEditor()){
            $fileName = post('file');
            $template = Content::load($this->getTheme(), $fileName);
            $template->fill([
                'fileName' => $fileName,
                'markup' => post('content')
            ]);
            $template->save();
        }
    }
    public function checkEditor()
    {
        $backendUser = BackendAuth::getUser();
        return $backendUser && $backendUser->hasAccess('cms.manage_content');
    }
}
