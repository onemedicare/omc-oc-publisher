<?php namespace Omc\Publisher\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOmcPublisherFeedbacks extends Migration
{
    public function up()
    {
        Schema::create('omc_publisher_feedbacks', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('token', 10);
            $table->string('type')->nullable()->default('Feedback');
            $table->string('name');
            $table->string('email');
            $table->string('tel')->nullable();
            $table->text('title');
            $table->text('content')->nullable();
            $table->string('status')->default('New');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('omc_publisher_feedbacks');
    }
}
