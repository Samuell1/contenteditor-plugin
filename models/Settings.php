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
            'bold'           => 'bold',
            'italic'         => 'italic',
            'link'           => 'link',

            'align-left'     => 'align-left',
            'align-center'   => 'align-center',
            'align-right'    => 'align-right',

            'heading'        => 'heading (h1)',
            'subheading'     => 'subheading (h2)',

            'subheading3'    => 'subheading3 (h3)',
            'subheading4'    => 'subheading4 (h4)',
            'subheading5'    => 'subheading5 (h5)',

            'paragraph'      => 'paragraph',
            'unordered-list' => 'unordered-list',
            'ordered-list'   => 'ordered-list',

            'table'          => 'table',
            'indent'         => 'indent',
            'unindent'       => 'unindent',
            'line-break'     => 'line-break',

            'image'          => 'image',
            'video'          => 'video',
            'preformatted'   => 'preformatted',
        ];
    }

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
}
