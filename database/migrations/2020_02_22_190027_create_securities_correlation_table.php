<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecuritiesCorrelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('securities_correlation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('security_id')->unsigned()->index();
            $table->foreign('security_id')->references('id')->on('securities');
            $table->integer('compared_security_id')->unsigned()->index();
            $table->foreign('compared_security_id')->references('id')->on('securities');
            $table->double('correlation', 8, 2);
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
        Schema::dropIfExists('securities_correlation');
    }
}
