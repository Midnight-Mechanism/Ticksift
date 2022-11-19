<?php

namespace Database\Seeders;

use App\Models\Action;
use App\Models\Security;
use Illuminate\Database\Seeder;

class ActionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filename = glob('stock_data/SHARADAR_ACTIONS*.csv')[0];
        $file = fopen($filename, 'r');

        $header = true;
        $chunk = [];
        $missing_tickers = [];

        while (($line = fgetcsv($file)) !== false) {
            if (! $header) {
                $action = Action::firstOrCreate(['name' => $line[1]]);
                $security = Security::where('ticker', $line[2])->first();
                if (! $security) {
                    if (! in_array($line[2], $missing_tickers)) {
                        $missing_tickers[] = $line[2];
                    }

                    continue;
                }
                $action->securities()->attach($security->id, [
                    'date' => $line[0],
                    'value' => $line[4],
                ]);
            } else {
                $header = false;
            }
        }

        fclose($file);
        if (count($missing_tickers) > 0) {
            $this->command->error('There was action data for unknown securities:');
            $this->command->error(implode(' ', $missing_tickers));
        }
    }
}
