<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\Security;
use Illuminate\Database\Seeder;

class PortfoliosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $portfolio = Portfolio::create([
            'name' => 'FAANG',
        ]);
        $portfolio->securities()->attach([
            Security::where('ticker', 'FB')->first()->id,
            Security::where('ticker', 'AAPL')->first()->id,
            Security::where('ticker', 'AMZN')->first()->id,
            Security::where('ticker', 'NFLX')->first()->id,
            Security::where('ticker', 'GOOG')->first()->id,
        ]);
    }
}
