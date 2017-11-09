<?php namespace Omc\Publisher\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'omc_publisher_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
}