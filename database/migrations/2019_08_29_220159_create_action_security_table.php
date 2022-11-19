<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActionSecurityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_security', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date')->index();
            $table->integer('action_id')->unsigned()->index();
            $table->foreign('action_id')->references('id')->on('actions');
            $table->integer('security_id')->unsigned()->index();
            $table->foreign('security_id')->references('id')->on('securities');
            $table->double('value', 8, 5);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_security');
    }
}
