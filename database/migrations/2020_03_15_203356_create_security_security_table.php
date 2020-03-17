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
            SELECT
            a.security_id AS security_id,
            b.security_id AS comp_security_id,
            corr(a.close, b.close) AS correlation
            FROM securities s
            INNER JOIN prices a
            ON a.security_id = s.id
            INNER JOIN prices b
            ON a.security_id = s.id
            AND a.date = b.date
            WHERE s.scale_marketcap >= 6
            GROUP BY a.security_id, b.security_id
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
