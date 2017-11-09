<?php namespace Omc\Publisher\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOmcPublisherContents extends Migration
{
    public function up()
    {
        Schema::create('omc_publisher_contents', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('type')->default('news');
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->text('body')->nullable();
            $table->string('feature_img')->nullable();
            $table->string('slug');
            $table->boolean('is_published')->nullable()->default(0);
            $table->dateTime('published_date')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_approved')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('author')->nullable();
            $table->dateTime('event_date_from')->nullable();
            $table->dateTime('event_date_to')->nullable();
            $table->string('event_venue')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('omc_publisher_contents');
    }
}
