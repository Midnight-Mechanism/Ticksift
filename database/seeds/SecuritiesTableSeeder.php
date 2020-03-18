<?php

use Illuminate\Database\Seeder;
use App\Models\SourceTable;
use App\Models\Exchange;
use App\Models\Currency;
use App\Models\Category;
use App\Models\SicSector;
use App\Models\SicIndustry;
use App\Models\Sector;
use App\Models\Industry;
use App\Models\Security;
use App\Models\Cusip;

class SecuritiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filename = glob('stock_data/SHARADAR_TICKERS*.csv')[0];
        $file = fopen($filename,"r");

        $header = TRUE;
        while (($line = fgetcsv($file)) !== FALSE) {
            if (!$header) {
                $source_table_id = SourceTable::firstOrCreate(['name' => $line[0]])->id;
                $exchange_id = Exchange::firstOrCreate(['name' => $line[4]])->id;
                $category_id = Category::firstOrCreate(['name' => $line[6]])->id;
                $cusips = explode(' ', $line[7]);
                if($line[8]) {
                    $sic_sector_id = SicSector::firstOrCreate(
                        ['code' => $line[8]],
                        ['name' => $line[9]]
                    )->id;
                    $sic_industry_id = SicIndustry::firstOrCreate(
                        [
                            'name' => $line[10],
                            'sic_sector_id' => $sic_sector_id,
                        ]
                    )->id;
                }
                $sector_id = Sector::firstOrCreate(['name' => $line[13]])->id;
                $industry_id = Industry::firstOrCreate([
                    'sector_id' => $sector_id,
                    'name' => $line[14],
                ])->id;
                $related_tickers = explode(' ', $line[17]);
                $currency_id = Currency::firstOrCreate(['code' => $line[18]])->id;

                $security = Security::updateOrCreate(
                    [
                        'source_id' => $line[1]
                    ],
                    [
                        'source_table_id' => $source_table_id,
                        'ticker' => $line[2],
                        'name' => $line[3],
                        'exchange_id' => $exchange_id,
                        'is_delisted' => $line[5],
                        'category_id' => $category_id,
                        'sic_industry_id' => isset($sic_industry_id) ? $sic_industry_id : null,
                        'industry_id' => $industry_id,
                        'scale_marketcap' => intval($line[15]),
                        'scale_revenue' => intval($line[16]),
                        'currency_id' => $currency_id,
                        'location' => $line[19] ?: null,
                        'source_last_updated' => $line[20],
                        'source_first_added' => $line[21],
                        'first_quarter' => $line[24] ?: null,
                        'last_quarter' => $line[25] ?: null,
                        'sec_filing_url' => $line[26] ?: null,
                        'company_url' => $line[27] ?: null,
                    ]
                );

                foreach ($cusips as $cusip) {
                    if ($cusip) {
                        Cusip::firstOrCreate([
                            'security_id' => $security->id,
                            'number' => $cusip,
                        ]);
                    }
                }
            } else {
                $header = FALSE;
            }
        }

        fclose($file);
    }
}
