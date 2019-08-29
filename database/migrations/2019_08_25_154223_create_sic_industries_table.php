<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSicIndustriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sic_industries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sic_sector_id')->unsigned()->index();
            $table->foreign('sic_sector_id')->references('id')->on('sic_sectors');
            $table->string('name');
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
        Schema::dropIfExists('sic_industries');
    }
}
