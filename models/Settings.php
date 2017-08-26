<?php namespace Samuell\ContentEditor\Models;

use File;
use Lang;
use Model;
use Less_Parser;
use Cache;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'samuell_contenteditor_settings';

    public $settingsFields = 'fields.yaml';

    const CACHE_KEY = 'samuell:contenteditor.additional_styles';

    public function initSettingsData() {
        $this->additional_styles = File::get(plugins_path().'/samuell/contenteditor/assets/additional-css.css');
    }

    // list of buttons
    public function getEnabledButtonsOptions() {
        return [
            'bold'           => 'Bold (b)',
            'italic'         => 'Italic (i)',
            'link'           => 'Link (a)',

            'align-left'     => 'Align left',
            'align-center'   => 'Align center',
            'align-right'    => 'Align right',

            'heading'        => 'Heading (h1)',
            'subheading'     => 'Subheading (h2)',

            'subheading3'    => 'Subheading3 (h3)',
            'subheading4'    => 'Subheading4 (h4)',
            'subheading5'    => 'Subheading5 (h5)',

            'paragraph'      => 'Paragraph (p)',
            'unordered-list' => 'Unordered list (ul)',
            'ordered-list'   => 'Ordered list (ol)',

            'table'          => 'Table',
            'indent'         => 'Indent',
            'unindent'       => 'Unindent',
            'line-break'     => 'Line-break (br)',

            'image'          => 'Image upload',
            'video'          => 'Video',
            'preformatted'   => 'Preformatted (pre)',
        ];
    }

    // list of allowed tags
    public function getAllowedTagsOptions() {
        return [
            'p',
            'img',
            'div',
            'table',
            'span',
            'small',

            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',

            'b',
            'i',
            'strong',
        ];
    }

    public function afterSave()
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function renderCss()
    {
        if (Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }
        try {
            $customCss = self::compileCss();
            Cache::forever(self::CACHE_KEY, $customCss);
        }
        catch (Exception $ex) {
            $customCss = '/* ' . $ex->getMessage() . ' */';
        }
        return $customCss;
    }
    public static function compileCss()
    {
        $parser = new Less_Parser(['compress' => true]);
        $customStyles = self::get('additional_styles');
        $parser->parse($customStyles);
        $css = $parser->getCss();
        return $css;
    }
}
