<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSecuritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('securities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_table_id')->unsigned()->index();
            $table->foreign('source_table_id')->references('id')->on('source_tables');
            $table->integer('source_id')->unsigned()->unique()->index();
            $table->string('ticker')->index();
            $table->string('name');
            $table->integer('exchange_id')->unsigned()->index();
            $table->foreign('exchange_id')->references('id')->on('exchanges');
            $table->boolean('is_delisted');
            $table->integer('category_id')->unsigned()->index();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->integer('sic_industry_id')->unsigned()->index()->nullable();
            $table->foreign('sic_industry_id')->references('id')->on('sic_industries');
            $table->integer('industry_id')->unsigned()->index();
            $table->foreign('industry_id')->references('id')->on('industries');
            $table->tinyInteger('scale_marketcap')->unsigned();
            $table->tinyInteger('scale_revenue')->unsigned();
            $table->integer('currency_id')->unsigned()->index();
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->string('location')->nullable();
            $table->date('source_first_added');
            $table->date('source_last_updated');
            $table->date('first_quarter')->nullable();
            $table->date('last_quarter')->nullable();
            $table->string('sec_filing_url')->nullable();
            $table->string('company_url')->nullable();
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
        Schema::dropIfExists('securities');
    }
}
