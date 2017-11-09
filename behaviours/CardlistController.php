<?php namespace Omc\Publisher\Behaviors;

use Str;
use Lang;
use Event;
use Flash;
use ApplicationException;
use Backend\Classes\ControllerBehavior;

/**
 * Adds features for working with backend list displayed in cards.
 *
 * This behavior is implemented in the controller like so:
 *
 *     public $implement = [
 *         'Omc.Pulisher.Behaviors.CardlistController',
 *     ];
 *
 *     public $cardlistConfig = 'config_cardlist.yaml';
 *
 * The `$cardlistConfig` property makes reference to the card list configuration
 * values as either a YAML file, located in the controller view directory,
 * or directly as a PHP array.
 *
 * @package omc/publisher
 * @author Azahari Zaman
 */
 
class cardlistController extends ControllerBehavior
{
    /**
     * @var array List definitions, keys for alias and value for configuration.
     */
    protected $cardlistDefinitions;

    /**
     * @var string The primary list alias to use. Default: list
     */
    protected $primaryDefinition;

    /**
     * @var array List configuration, keys for alias and value for config objects.
     */
    protected $cardlistConfig = [];

    /**
     * @var \Backend\Classes\WidgetBase Reference to the list widget object.
     */
    protected $cardlistWidgets = [];
    
    /**
     * @inheritDoc
     */
    protected $requiredProperties = ['cardlistConfig'];

    /**
     * @var array Configuration values that must exist when applying the primary config file.
     * - modelClass: Class name for the model
     * - cardlist: Card List elements/columns definitions
     */
    protected $requiredConfig = ['modelClass', 'cardlist'];
    
    /**
     * Behavior constructor
     * @param \Backend\Classes\Controller $controller
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        /*
         * Extract list definitions
         */
        if (is_array($controller->cardlistConfig)) {
            $this->cardlistDefinitions = $controller->cardlistConfig;
            $this->primaryDefinition = key($this->cardlistDefinitions);
        }
        else {
            $this->cardlistDefinitions = ['cardlist' => $controller->cardlistConfig];
            $this->primaryDefinition = 'cardlist';
        }

        /*
         * Build configuration
         */
        $this->setConfig($this->cardlistDefinitions[$this->primaryDefinition], $this->requiredConfig);
    }
    
    /**
     * Creates all the cardlist widgets based on the definitions.
     * @return array
     */
    public function makeCardlists()
    {
        foreach ($this->cardlistDefinitions as $definition => $config) {
            $this->cardlistWidgets[$definition] = $this->makecardlist($definition);
        }

        return $this->cardlistWidgets;
    }
    
}