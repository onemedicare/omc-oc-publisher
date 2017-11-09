<?php namespace Omc\Publisher\Models;

use Model;

/**
 * Model
 */
class Feedback extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];
    
    public function beforeCreate()
    {
        $this->token = str_random(10);
    }

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'omc_publisher_feedbacks';
}