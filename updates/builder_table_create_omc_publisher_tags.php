<?php namespace Omc\Publisher\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOmcPublisherTags extends Migration
{
    public function up()
    {
        Schema::create('omc_publisher_tags', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('omc_publisher_tags');
    }
}
