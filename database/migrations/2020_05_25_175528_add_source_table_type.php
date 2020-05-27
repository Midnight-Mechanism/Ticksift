<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSourceTableType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('source_tables', function (Blueprint $table) {
            $table->string('group')->nullable();
        });
        DB::table('source_tables')
            ->where('name', 'SEP')
            ->update(['group' => 'Securities']);
        DB::table('source_tables')
            ->where('name', 'SFP')
            ->update(['group' => 'Funds']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('source_tables', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
}
