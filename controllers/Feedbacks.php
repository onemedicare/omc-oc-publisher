<?php namespace Omc\Publisher\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Feedbacks extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'manage_feedbacks' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Omc.Publisher', 'main-menu-item', 'feedback');
    }
}