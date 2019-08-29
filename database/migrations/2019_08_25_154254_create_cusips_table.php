<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCusipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cusips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('security_id')->unsigned()->index();
            $table->foreign('security_id')->references('id')->on('securities');
            $table->string('number')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cusips');
    }
}
