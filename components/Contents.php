<?php namespace Omc\Publisher\Components;

use Cms\Classes\ComponentBase;
use Omc\Publisher\Models\Content as ContentModel;

class Contents extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Contents Component',
            'description' => 'Content component for the Publisher Plugin'
        ];
    }

    public function defineProperties()
    {
        return [
            'contentType' => [
                 'title'             => 'Content Type',
                 'description'       => 'Specify which contents to render',
                 'default'           => 'news',
                 'type'              => 'string',
            ]
        ];
    }
    
    public function publishedContents()
    {
        if($postSlug = $this->param('slug')) {
            $content = ContentModel::where('slug', $postSlug)->get();
            
            return $content;
        }
        
        $contents = ContentModel::where('is_published', true)
                    ->where('type', $this->property('contentType'))
                    ->orderBy('published_date', 'desc')
                    ->get();
        
        return $contents;
    }
}
