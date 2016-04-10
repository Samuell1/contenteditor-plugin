<?php namespace Samuell\ContentEditor;

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
             'name'        => 'ContentEditor',
             'description' => 'Frontend content editor',
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
}
