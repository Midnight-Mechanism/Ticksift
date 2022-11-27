<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CascadePortfolioRelationships extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portfolio_security', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('portfolio_security_portfolio_id_foreign');
                $table->dropForeign('portfolio_security_security_id_foreign');
            }
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->onDelete('cascade');
            $table->foreign('security_id')->references('id')->on('securities')->onDelete('cascade');
        });
        Schema::table('portfolio_user', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('portfolio_user_portfolio_id_foreign');
                $table->dropForeign('portfolio_user_user_id_foreign');
            }
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_security', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('portfolio_security_portfolio_id_foreign');
                $table->dropForeign('portfolio_security_security_id_foreign');
            }
            $table->foreign('portfolio_id')->references('id')->on('portfolios');
            $table->foreign('security_id')->references('id')->on('securities');
        });
        Schema::table('portfolio_user', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('portfolio_user_portfolio_id_foreign');
                $table->dropForeign('portfolio_user_user_id_foreign');
            }
            $table->foreign('portfolio_id')->references('id')->on('portfolios');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
