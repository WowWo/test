<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('employee', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dep_id')->unsigned();
            $table->string('full_name');
            $table->integer('salary');
        });
        Schema::table('employee', function($table) {
            $table->foreign('dep_id')->references('id')->on('department');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('employee');
    }

}
