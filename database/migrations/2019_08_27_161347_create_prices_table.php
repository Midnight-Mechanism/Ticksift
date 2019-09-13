<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('security_id')->unsigned()->index();
            $table->foreign('security_id')->references('id')->on('securities');
            $table->date('date')->index();
            $table->double('open', 8, 2);
            $table->double('high', 8, 2);
            $table->double('low', 8, 2);
            $table->double('close', 8, 2);
            $table->double('volume', 8, 2)->nullable()->index();
            $table->double('dividends', 8, 2);
            $table->double('close_unadj', 8, 2);
            $table->date('source_last_updated');
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
        Schema::dropIfExists('prices');
    }
}
