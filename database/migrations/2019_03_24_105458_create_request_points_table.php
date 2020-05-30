<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_type_request', function (Blueprint $table) {
            $table->integer('request_id')->unsigned();
            $table->integer('point_id')->unsigned();
            $table->float('weight');
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('requests');            
            $table->foreign('point_id')->references('id')->on('point_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_point');
    }
}
