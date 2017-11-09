<?php namespace Omc\Publisher\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOmcPublisherContentTag extends Migration
{
    public function up()
    {
        Schema::create('omc_publisher_content_tag', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('content_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->primary(['content_id','tag_id']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('omc_publisher_content_tag');
    }
}
