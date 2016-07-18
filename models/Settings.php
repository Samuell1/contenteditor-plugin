<?php namespace Samuell\ContentEditor\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'samuell_contenteditor_settings';

    public $settingsFields = 'fields.yaml';
}
