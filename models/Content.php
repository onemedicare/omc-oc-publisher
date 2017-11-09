<?php namespace Omc\Publisher\Models;

use Model;

/**
 * Model
 */
class Content extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;
    
    use \October\Rain\Database\Traits\Sluggable;

    protected $dates = ['deleted_at', 'published_date', 'expiry_date', 'event_date_from', 'event_date_to', 'approved_date'];
    
    protected $slugs = ['slug' => 'title'];

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'omc_publisher_contents';
    
    public $belongsToMany = [
        'tags' => ['Omc\Publisher\Models\Tag', 'table' => 'omc_publisher_content_tag'],
    ];
    
    public function beforeSave()
    {
        $this->excerpt = $this->strWordCut($this->body, 170, '...');
    }
    
    protected function strWordCut($string, $length, $end='....')
    {
        $string = strip_tags($string);
    
        if (strlen($string) > $length) {
    
            // truncate string
            $stringCut = substr($string, 0, $length);
    
            // make sure it ends in a word so assassinate doesn't become ass...
            $string = substr($stringCut, 0, strrpos($stringCut, ' ')).$end;
        }
        return $string;
    }
    
    public function filterFields($fields, $context = null)
    {
        if ($context == 'preview') {
            $fields->title->comment = 'http://www.one-medicare.com/news/' . $this->slug;
        }
    }
    
    public function unPublish()
    {
        $this->is_published = 0;
        
        $this->save();
        
        return;
    }
    
    public function publish()
    {
        $this->is_published = 1;
        $this->save();
        
        return;
    }
}