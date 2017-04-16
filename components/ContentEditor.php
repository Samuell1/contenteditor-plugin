<?php namespace Samuell\ContentEditor\Components;

use BackendAuth;

use Cms\Classes\Content;
use Cms\Classes\CmsObject;
use Cms\Classes\ComponentBase;

use Samuell\ContentEditor\Models\Settings;

class ContentEditor extends ComponentBase
{
    public $content;
    public $file;
    public $fixture;
    public $tools;
    public $buttons;
    public $palettes;

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
            ],
            'fixture' => [
                'title'       => 'Content block tag with disabled toolbox',
                'description' => 'Fixed name for content block, useful for inline texts (headers, spans...)',
            ],
            'tools' => [
                'title'       => 'List of enabled tools',
                'description' => 'List of enabled tools for selected content (for all use *)',
                'default'     => '*',
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
            $this->palettes = Settings::get('style_palettes');

            // put content tools js + css
            $this->addCss('assets/content-tools.css');
            $this->addJs('assets/content-tools.min.js');
            $this->addJs('assets/contenteditor.js');
        }
    }

    public function onRender()
    {

        $this->file = $this->property('file');
        $this->fixture = $this->property('fixture');
        $this->tools = $this->property('tools');

        // Compatability with RainLab.Translate
        if (class_exists('\RainLab\Translate\Classes\Translator')) {
            $locale = \RainLab\Translate\Classes\Translator::instance()->getLocale();
            $fileName = substr_replace($this->file, '.'.$locale, strrpos($this->file, '.'), 0);
            if (($content = Content::loadCached($this->page->controller->getTheme(), $fileName)) !== null)
                $this->file = $fileName;
        }

        if ($this->checkEditor()) {

            if (Content::load($this->getTheme(), $this->file)){
                $this->content = $this->renderContent($this->file);
            } else {
                $this->content = '';
            }

        } else {
            return $this->renderContent($this->file);
        }
    }

    public function onSave()
    {
        if ($this->checkEditor()) {

            $fileName = post('file');

            if ($load = Content::load($this->getTheme(), $fileName)) {
                $fileContent = $load; // load existed content file
            } else {
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
