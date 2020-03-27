<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowNullSecurityData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('securities', function (Blueprint $table) {
            $table->integer('source_id')->unsigned()->nullable()->change();
            $table->string('ticker')->nullable()->change();
            $table->integer('exchange_id')->unsigned()->nullable()->change();
            $table->integer('category_id')->unsigned()->nullable()->change();
            $table->integer('industry_id')->unsigned()->nullable()->change();
            $table->date('source_first_added')->nullable()->change();
            $table->date('source_last_updated')->nullable()->change();
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->float('open', 8, 2)->nullable()->change();
            $table->float('high', 8, 2)->nullable()->change();
            $table->float('low', 8, 2)->nullable()->change();
            $table->float('dividends', 8, 2)->nullable()->change();
            $table->date('source_last_updated')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('securities', function (Blueprint $table) {
            $table->integer('source_id')->unsigned()->change();
            $table->string('ticker')->change();
            $table->integer('exchange_id')->unsigned()->change();
            $table->integer('category_id')->unsigned()->change();
            $table->integer('industry_id')->unsigned()->change();
            $table->date('source_first_added')->change();
            $table->date('source_last_updated')->change();
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->float('open', 8, 2)->change();
            $table->float('high', 8, 2)->change();
            $table->float('low', 8, 2)->change();
            $table->float('dividends', 8, 2)->change();
            $table->date('source_last_updated')->change();
        });
    }
}
