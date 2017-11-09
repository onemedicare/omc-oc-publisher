<?php namespace Omc\Publisher;

use System\Classes\PluginBase;
use Backend;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Omc\Publisher\Components\Contents' => 'contents'
        ];
    }
    
    public function registerFormWidgets()
    {
        return [
            'Tsi\Spacesuit\FormWidgets\Suilabel' => 'suilabel',
        ];
    }

    
    public function registerSettings()
    {
        return [
            'OMC' => [
                'label'       => 'Settings',
                'description' => 'Manage user based settings.',
                'category'    => 'OMC',
                'icon'        => 'icon-cog',
                'class'       => 'Omc\Publisher\Models\Settings',
                'order'       => 500,
                'keywords'    => 'security location',
                'permissions' => ['acme.users.access_settings']
            ]
        ];
    }
}
