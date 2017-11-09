<?php namespace Omc\Publisher\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Tags extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'manage_tags' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Omc.Publisher', 'main-menu-item', 'side-menu-item-tag');
    }
}