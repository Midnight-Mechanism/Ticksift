<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecuritySecurityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE MATERIALIZED VIEW security_security AS
            WITH a AS (
                SELECT p.security_id AS security_id, p.date AS sec_date, p.close AS closing_cost
                FROM prices p
            ), b AS (
                SELECT p.security_id AS comp_security_id, p.date AS comp_sec_date, p.close AS comp_closing_cost
                FROM prices p
            )
            SELECT a.security_id AS security_id, b.comp_security_id AS comp_security_id, corr(a.closing_cost, b.comp_closing_cost)
                FROM a JOIN b
                    ON a.sec_date = b.comp_sec_date
                    WHERE a.security_id < b.comp_security_id
                GROUP BY a.security_id, b.comp_security_id
                ORDER BY a.security_id, b.comp_security_id
            WITH NO DATA");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP MATERIALIZED VIEW security_security");
    }
}
