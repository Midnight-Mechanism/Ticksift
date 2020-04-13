<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActionContraticker extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('action_security', function (Blueprint $table) {
            $table->string('contraticker')->nullable();
            $table->unique([
                'date',
                'action_id',
                'security_id',
                'contraticker',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_security', function (Blueprint $table) {
            $table->dropUnique('action_security_date_action_id_security_id_contraticker_unique');
            $table->dropColumn('contraticker');
        });
    }
}
