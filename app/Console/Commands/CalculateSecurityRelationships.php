<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use DB;

class CalculateSecurityRelationships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'securities:calculate-relationships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate security relationships';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY security_security');
        } catch (QueryException $e) {
            DB::statement('REFRESH MATERIALIZED VIEW security_security');
        }
        \Log::info('Finished calculating security relationships');
    }
}
