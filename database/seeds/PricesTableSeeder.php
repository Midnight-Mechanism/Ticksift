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
        $filename = glob('stock_data/SHARADAR_SEP*.csv')[0];
        $file = fopen($filename,"r");

        $header = TRUE;
        $chunk = [];
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
                    $this->command->error($e->getMessage());
                    $this->command->error(implode(',', $line));
                    continue;
                }
                if (count($chunk) > 1000) {
                    $this->command->info(implode(',', $line));
                    Price::insert($chunk);
                    $chunk = [];
                }
            } else {
                $header = FALSE;
            }
        }

        fclose($file);
    }
}
