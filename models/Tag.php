<?php namespace Omc\Publisher\Models;

use Model;

/**
 * Model
 */
class Tag extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];
    
    protected $fillable = ['name'];

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'omc_publisher_tags';
    
    public $belongsToMany = [
        'contents' => ['Omc\Publisher\Models\Content', 'table' => 'omc_publisher_content_tag'],
    ];
}