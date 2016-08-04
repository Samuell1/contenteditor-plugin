<?php namespace Samuell\ContentEditor;

use App;
use Event;
use Backend;
use System\Classes\PluginBase;

/**
 * ContentEditor Plugin Information File
 */
class Plugin extends PluginBase
{

      /**
       * Returns information about this plugin.
       *
       * @return array
       */
      public function pluginDetails()
      {
         return [
             'name'        => 'Content Editor',
             'description' => 'Front-end content editor',
             'author'      => 'Samuell',
             'icon'        => 'icon-edit'
         ];
      }

      public function registerComponents()
      {
         return [
             'Samuell\ContentEditor\Components\ContentEditor' => 'contenteditor',
         ];
      }

      public function registerSettings()
      {
          return [
              'settings' => [
                  'label'       => 'Content Editor Settings',
                  'description' => 'Manage main editor settings.',
                  'icon'        => 'icon-cog',
                  'class'       => 'Samuell\ContentEditor\Models\Settings',
                  'order'       => 500,
                  'permissions' => ['samuell.contenteditor.access_settings']
              ]
          ];
      }
}
