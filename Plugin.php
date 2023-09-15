<?php

namespace Samuell\ContentEditor;

use Samuell\ContentEditor\Components\ContentEditor;
use Samuell\ContentEditor\Models\Settings;
use System\Classes\PluginBase;

/**
 * ContentEditor Plugin Information File
 */
class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name' => 'Content Editor',
            'description' => 'Front-end content editor',
            'author' => 'Samuell',
            'icon' => 'icon-edit'
        ];
    }

    public function registerComponents(): array
    {
        return [
            ContentEditor::class => 'contenteditor',
        ];
    }

    public function registerSettings(): array
    {
        return [
            'settings' => [
                'label' => 'Content Editor Settings',
                'description' => 'Manage main editor settings.',
                'icon' => 'icon-cog',
                'class' => Settings::class,
                'order' => 500,
                'permissions' => ['samuell.contenteditor.access_settings']
            ]
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'samuell.contenteditor.editor' => [
                'tab' => 'Content Editor',
                'label' => 'Allow to use content editor on frontend'
            ],
            'samuell.contenteditor.access_settings' => [
                'tab' => 'Content Editor',
                'label' => 'Access content editor settings'
            ],
        ];
    }
}
