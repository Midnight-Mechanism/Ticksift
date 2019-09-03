<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortfolioSecurityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portfolio_security', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('portfolio_id')->unsigned()->index();
            $table->foreign('portfolio_id')->references('id')->on('portfolios');
            $table->integer('security_id')->unsigned()->index();
            $table->foreign('security_id')->references('id')->on('securities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portfolio_security');
    }
}
