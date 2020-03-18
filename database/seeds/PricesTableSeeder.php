<?php

use Illuminate\Database\Seeder;
use App\Models\Security;
use App\Models\Price;
use Carbon\Carbon;

class PricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (['SEP', 'SFP'] as $source_table) {
            $filename = glob('stock_data/SHARADAR_' . $source_table . '*.csv')[0];
            $file = fopen($filename,"r");

            $header = TRUE;
            $chunk = [];
            $insertCount = 0;
            $missing_tickers = [];
            while (($line = fgetcsv($file)) !== FALSE) {
                if (!$header) {
                    $security = Security::where('ticker', $line[0])->first();
                    try {
                        $chunk[] = [
                            'security_id' => $security->id,
                            'date' => $line[1],
                            'open' => $line[2],
                            'high' => $line[3],
                            'low' => $line[4],
                            'close' => $line[5],
                            'volume' => $line[6] ?: null,
                            'dividends' => $line[7],
                            'close_unadj' => $line[8],
                            'source_last_updated' => $line[9],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    } catch (Exception $e) {
                        if (!in_array($line[0], $missing_tickers)) {
                            $missing_tickers[] = $line[0];
                        }
                        continue;
                    }
                    if (count($chunk) > 1000) {
                        $insertCount = $insertCount + count($chunk);
                        Price::insert($chunk);
                        $chunk = [];
                        \Log::info($insertCount . ' inserted');
                    }
                } else {
                    $header = FALSE;
                }
            }

            $insertCount = $insertCount + count($chunk);
            Price::insert($chunk);
            $chunk = [];
            \Log::info($insertCount . ' inserted');

            fclose($file);
            if (count($missing_tickers) > 0) {
                $this->command->error("There was price data for unknown securities:");
                $this->command->error(implode(' ', $missing_tickers));
            }
        }
    }
}
