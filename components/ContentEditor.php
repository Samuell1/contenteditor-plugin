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
    public $fixture;
    public $tools;
    public $buttons;
    public $palettes;

    public $renderCount;
    public $additional_styles;

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
                'type'        => 'dropdown'
            ],
            'fixture' => [
                'title'       => 'Content block tag with disabled toolbox',
                'description' => 'Fixed name for content block, useful for inline texts (headers, spans...)',
                'default'     => ''
            ],
            'tools' => [
                'title'       => 'List of enabled tools',
                'description' => 'List of enabled tools for selected content (for all use *)',
                'default'     => ''
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
            $this->addCss('assets/content-tools.min.css');
            $this->addCss('assets/contenteditor.css');
            $this->addJs('assets/content-tools.min.js');
            $this->addJs('assets/contenteditor.js');
        }
    }

    public function onRender()
    {
        $this->additional_styles = Settings::renderCss();
        $this->renderCount += 1;

        $this->file = $this->setFile($this->property('file'));
        $this->fixture = $this->property('fixture');
        $this->tools = $this->property('tools');

        $content = $this->getFile();

        if ($this->checkEditor()) {
            $this->content = $content;
        } else {
            return $this->renderPartial('@render.htm', ['content' => $content]);
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

    public function getFile()
    {
        // if no locale file exists -> render the default, without language suffix
        if (Content::load($this->getTheme(), $this->file)) {
            return $this->renderContent($this->file);
        }

        return '';
    }

    public function setFile($file)
    {
        // Compatability with RainLab.Translate
        if ($this->translateExists()) {
            return $this->setTranslateFile($file);
        }

        return $file;
    }

    public function setTranslateFile($file)
    {
        $translate = \RainLab\Translate\Classes\Translator::instance();
        $defaultLocale = $translate->getDefaultLocale();
        $locale = $translate->getLocale();

        // Compability with Rainlab.StaticPage
        // StaticPages content does not append default locale to file.
        if ($this->fileExists($file) && $locale === $defaultLocale) {
          return $file;
        }

        return substr_replace($file, '.'.$locale, strrpos($file, '.'), 0);
    }

    public function checkEditor()
    {
        $backendUser = BackendAuth::getUser();
        return $backendUser && $backendUser->hasAccess(Settings::get('permissions', 'cms.manage_content'));
    }

    public function fileExists($file) {
        return File::exists((new Content)->getFilePath($file));
    }

    public function translateExists()
    {
        return class_exists('\RainLab\Translate\Classes\Translator');
    }
}
