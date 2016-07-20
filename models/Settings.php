<?php namespace Samuell\ContentEditor\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'samuell_contenteditor_settings';

    public $settingsFields = 'fields.yaml';

    // list of buttons
    public function getEnabledButtonsOptions() {
        return [

            'bold'   => 'bold',
            'italic' => 'italic',
            'link'   => 'link',

            'align-left' => 'align-left',
            'align-center'   => 'align-center',
            'align-right' => 'align-right',

            'heading'   => 'heading (h1)',
            'subheading' => 'subheading (h2)',
            //'subheading3' => 'subheading3',
            //'subheading4' => 'subheading4',

            'paragraph'   => 'paragraph',
            'unordered-list' => 'unordered-list',
            'ordered-list'   => 'ordered-list',

            'table'   => 'table',
            'indent' => 'indent',
            'unindent'   => 'unindent',
            'line-break' => 'line-break',

            'image' => 'image',
            'video' => 'video',
            'preformatted' => 'preformatted',
        ];
    }
}
