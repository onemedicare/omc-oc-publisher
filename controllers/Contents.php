<?php namespace Omc\Publisher\Controllers;

use Backend\Classes\Controller;
use Omc\Publisher\Models\Content as ContentModel;
use BackendMenu;
use Flash;

class Contents extends Controller
{
    public $implement = ['Backend\Behaviors\ListController','Backend\Behaviors\FormController'];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'manage_contents' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Omc.Publisher', 'main-menu-item', 'side-menu-item-content');
        
    }
    
    /**
     * Overite the FormController preview method
     *
     * @return void
     * @author `Azahari Zaman`
     */
    public function preview($recordId)
    {
        $this->bodyClass = 'compact-container';
        
        return $this->asExtension('FormController')->preview($recordId);
    }
    
    // when contents are saved and mark as pending verification
    
    public function formExtendRefreshFields($form, $fields)
    {
        
    }
    
    public function onUnpublish($recordId, $context = '')
    {
        // Check if the record is_published. If yes, unpublish the record
        $content = ContentModel::where('id', $recordId)->first();
        Flash::success('You did it!');
        return $content->unPublish();
        
    }
    
    public function onPublish($recordId, $context = '')
    {
        // Check if content is not publish
        $content = ContentModel::where('id', $recordId)->first();
        Flash::success('You did it!');
        return $content->publish();
    }
    
    public function onEdit($recordId, $context = '')
    {
        return true;
    }
}