<?php namespace Omc\Publisher\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateOmcPublisherContents extends Migration
{
    public function up()
    {
        Schema::table('omc_publisher_contents', function($table)
        {
            $table->dateTime('approved_date')->nullable();
            $table->increments('id')->unsigned(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('omc_publisher_contents', function($table)
        {
            $table->dropColumn('approved_date');
            $table->increments('id')->unsigned()->change();
        });
    }
}
