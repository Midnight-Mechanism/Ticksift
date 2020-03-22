<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Portfolio;
use App\Models\Security;

class GenerateSystemPortfolios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portfolios:generate-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate system portfolios';

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
        $portfolio_faang = Portfolio::doesntHave('users')
            ->firstOrCreate([
                'name' => 'FAANG',
            ]);
        $portfolio_faang->securities()->sync([
            Security::where('ticker', 'FB')->first()->id,
            Security::where('ticker', 'AMZN')->first()->id,
            Security::where('ticker', 'AAPL')->first()->id,
            Security::where('ticker', 'NFLX')->first()->id,
            Security::where('ticker', 'GOOG')->first()->id,
        ]);

        \Log::info($portfolio_faang);
    }
}
