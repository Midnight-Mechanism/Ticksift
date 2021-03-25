<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Security;
use App\Models\Portfolio;

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
