<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Security;
use App\Models\SourceTable;
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
        $securities = Security::select('id', 'source_table_id', 'ticker')
            ->get()
            ->groupBy('source_table_id')
            ->mapWithKeys(function($sub_securities, $source_table_id) {
                return [
                    $source_table_id => $sub_securities->mapWithKeys(function($sub_security) {
                        return [$sub_security->ticker => $sub_security->id];
                    })
                ];
            });

        foreach ($securities as $source_table_id => $source_table_securities) {
            $filename = glob('stock_data/SHARADAR_' . SourceTable::findOrFail($source_table_id)->name . '*.csv')[0];
            $file = fopen($filename,"r");

            $header = TRUE;
            $chunk = [];
            $insertCount = 0;
            $missing_tickers = [];
            while (($line = fgetcsv($file)) !== FALSE) {
                if (!$header) {
                    $security_id = $source_table_securities->get($line[0]);
                    if (!empty($security_id)) {
                        $chunk[] = [
                            'security_id' => $security_id,
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
                    } else {
                        \Log::info($line[0]);
                        if (!in_array($line[0], $missing_tickers)) {
                            $missing_tickers[] = $line[0];
                        }
                    }
                    if (count($chunk) > 1000) {
                        $insertCount = $insertCount + count($chunk);
                        DB::table('prices')->insert($chunk);
                        $chunk = [];
                        \Log::info($insertCount . ' inserted');
                    }
                } else {
                    $header = FALSE;
                }
                unset($line);
            }

            $insertCount = $insertCount + count($chunk);
            DB::table('prices')->insert($chunk);
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
