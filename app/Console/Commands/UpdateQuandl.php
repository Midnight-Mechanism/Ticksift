<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SourceTable;
use App\Models\Exchange;
use App\Models\Category;
use App\Models\SicSector;
use App\Models\SicIndustry;
use App\Models\Sector;
use App\Models\Industry;
use App\Models\Currency;
use App\Models\Security;
use App\Models\Cusip;
use App\Models\Action;
use App\Models\Price;
use ZipArchive;

class UpdateQuandl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quandl:update {start_date? : The date to start from} {end_date? : The date to end at}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull the latest data from Quandl';

    /**
     * Whether to update momentum on completion
     *
     * @var boolean
     */
    private $update_momentum = FALSE;

    /**
     * The Quandl API key
     *
     * @var string
     */
    private $quandl_key;

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
        $this->quandl_key = env('QUANDL_KEY');

        $this->updateSharadarSecurities();
        $this->updateActions();
        $this->updateSharadarPrices();
        $this->updateFredPrices();
        $this->updateLondonPrices();
        $this->updateRecessions();
        if($this->update_momentum) {
            \Artisan::call('momentum:calculate-presets');
        }
    }

    private function fetchQuandlCSV($url, $params = [], $bulk_export = FALSE) {
        $params['api_key'] = $this->quandl_key;
        if ($bulk_export) {
            $params['qopts.export'] = TRUE;
        }

        $url .= '?' . http_build_query($params);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $results = curl_exec($curl);
        curl_close($curl);

        if ($bulk_export) {
            $results = json_decode($results, TRUE);
            if ($results && array_key_exists('datatable_bulk_download', $results)) {
                $bulk_link = $results['datatable_bulk_download']['file']['link'];
            } else {
                \Log::error('Could not get data at URL ' . $url);
                return [];
            }

            // save bulk download file
            $curl = curl_init();
            $zip_filename = tempnam(sys_get_temp_dir(), 'quandl_bulk_');
            $zip_file = fopen($zip_filename, 'w');
            curl_setopt($curl, CURLOPT_URL, $bulk_link);
            curl_setopt($curl, CURLOPT_FILE, $zip_file);
            curl_exec($curl);
            curl_close($curl);
            fclose($zip_file);

            // read bulk download file
            $zip = new ZipArchive();
            $zip->open($zip_filename);
            $results = $zip->getFromIndex(0);
            $zip->close();
        }

        $lines = array_filter(explode(PHP_EOL, $results));

        return($lines);
    }

    private function fetchQuandlDBMetadata($group) {
        $zip_filename = tempnam(sys_get_temp_dir(), 'quandl_meta_');
        $zip_file = fopen($zip_filename, 'w');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://www.quandl.com/api/v3/databases/' . $group . '/metadata?api_key=' . $this->quandl_key);
        curl_setopt($curl, CURLOPT_FILE, $zip_file);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($curl);
        curl_close($curl);

        fclose($zip_file);

        $zip = new ZipArchive();
        $zip->open($zip_filename);
        $results = $zip->getFromIndex(0);
        $zip->close();
        $lines = explode(PHP_EOL, $results);
        // delete header row
        array_shift($lines);
        return($lines);
    }

    private function updateSharadarSecurities() {
        // get link to bulk download file
        $url = 'https://www.quandl.com/api/v3/datatables/SHARADAR/TICKERS';
        $params = [];

        if ($this->argument('start_date')) {
            $params['lastupdated.gte'] = $this->argument('start_date');
        } else {
            $params['lastupdated.gte'] = Security::max('source_last_updated');
        }
        if ($this->argument('end_date')) {
            $params['lastupdated.lte'] = $this->argument('end_date');
        }

        $lines = $this->fetchQuandlCSV($url, $params, $bulk_export = TRUE);
        $header = str_getcsv(array_shift($lines));

        foreach ($lines as $line) {
            $line = array_combine($header, str_getcsv($line));
            if (!SourceTable::where('name', $line['table'])->exists()) {
                continue;
            }
            $source_table_id = SourceTable::where('name', $line['table'])->first()->id;
            $exchange_id = Exchange::firstOrCreate(['name' => $line['exchange']])->id;
            $category_id = Category::firstOrCreate(['name' => $line['category']])->id;
            $cusips = explode(' ', $line['cusips']);
            if($line['siccode']) {
                $sic_sector_id = SicSector::firstOrCreate(
                    ['code' => $line['siccode']],
                    ['name' => $line['sicsector']]
                )->id;
                $sic_industry_id = SicIndustry::firstOrCreate(
                    [
                        'name' => $line['sicindustry'],
                        'sic_sector_id' => $sic_sector_id,
                    ]
                )->id;
            }
            $sector_id = Sector::firstOrCreate(['name' => $line['sector']])->id;
            $industry_id = Industry::firstOrCreate([
                'sector_id' => $sector_id,
                'name' => $line['industry'],
            ])->id;
            $related_tickers = explode(' ', $line['relatedtickers']);
            $currency_id = Currency::firstOrCreate(['code' => $line['currency']])->id;

            $security = Security::updateOrCreate(
                [
                    'source_table_id' => $source_table_id,
                    'source_id' => $line['permaticker'],
                ],
                [
                    'ticker' => $line['ticker'],
                    'name' => $line['name'],
                    'exchange_id' => $exchange_id,
                    'is_delisted' => $line['isdelisted'],
                    'category_id' => $category_id,
                    'sic_industry_id' => isset($sic_industry_id) ? $sic_industry_id : null,
                    'industry_id' => $industry_id,
                    'scale_marketcap' => intval($line['scalemarketcap']),
                    'scale_revenue' => intval($line['scalerevenue']),
                    'currency_id' => $currency_id,
                    'location' => $line['location'] ?: null,
                    'source_last_updated' => $line['lastupdated'],
                    'source_first_added' => $line['firstadded'],
                    'first_quarter' => $line['firstquarter'] ?: null,
                    'last_quarter' => $line['lastquarter'] ?: null,
                    'sec_filing_url' => $line['secfilings'] ?: null,
                    'company_url' => $line['companysite'] ?: null,
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
        }

        \Log::info('Security data successfully updated from Quandl.');
    }

    private function updateActions() {
        $url = 'https://www.quandl.com/api/v3/datatables/SHARADAR/ACTIONS';
        $params = [];

        if ($this->argument('start_date')) {
            $params['date.gte'] = $this->argument('start_date');
        } else {
            $params['date.gte'] = \DB::table('action_security')->max('date');
        }
        if ($this->argument('end_date')) {
            $params['date.lte'] = $this->argument('end_date');
        }

        $lines = $this->fetchQuandlCSV($url, $params, $bulk_export = TRUE);
        $header = str_getcsv(array_shift($lines));

        $chunk = [];
        foreach ($lines as $line) {
            $line = array_combine($header, str_getcsv($line));
            $action = Action::firstOrCreate(['name' => $line['action']]);
            $security = Security::where('ticker', $line['ticker'])->first();
            if ($security) {
                $chunk[] = [
                    'date' => $line['date'],
                    'action_id' => $action->id,
                    'security_id' => $security->id,
                    'value' => $line['value'] ?: null,
                    'contraticker' => $line['contraticker'] ?: null,
                ];
            }
            if (count($chunk) > 1000) {
                \DB::table('action_security')->upsert($chunk, [
                    'date',
                    'action_id',
                    'security_id',
                    'contraticker',
                ]);
                $chunk = [];
            }
        }
        \DB::table('action_security')->upsert($chunk, [
            'date',
            'action_id',
            'security_id',
            'contraticker',
        ]);
        \Log::info('Action data successfully updated from Quandl.');
    }

    private function updateSharadarPrices() {
        // get link to bulk download file
        foreach(['SEP', 'SFP'] as $source_table_name) {
            $source_table = SourceTable::where('name', $source_table_name)->first();
            $newest_price_updated = Price::sourceTableFilter($source_table->name)->max('source_last_updated');
            $url = 'https://www.quandl.com/api/v3/datatables/SHARADAR/' . $source_table->name;
            $params = [];

            if ($this->argument('start_date')) {
                $params['lastupdated.gte'] = $this->argument('start_date');
            } else {
                $params['lastupdated.gte'] = Price::sourceTableFilter($source_table->name)->max('source_last_updated');
            }
            if ($this->argument('end_date')) {
                $params['lastupdated.lte'] = $this->argument('end_date');
            }

            $lines = $this->fetchQuandlCSV($url, $params, $bulk_export = TRUE);
            $header = str_getcsv(array_shift($lines));

            $chunk = [];
            foreach ($lines as $line) {
                $line = array_combine($header, str_getcsv($line));
                $security = Security::where('source_table_id', $source_table->id)
                    ->where('ticker', $line['ticker'])
                    ->first();
                if ($security) {
                    $chunk[] = [
                        'security_id' => $security->id,
                        'date' => $line['date'],
                        'open' => $line['open'],
                        'high' => $line['high'],
                        'low' => $line['low'],
                        'close' => $line['close'],
                        'volume' => $line['volume'] ?: null,
                        'close_adj' => $line['closeadj'] ?: null,
                        'close_unadj' => $line['closeunadj'],
                        'source_last_updated' => $line['lastupdated'],
                    ];
                } else {
                    \Log::info('Security ' . $line['ticker'] . ' on table ' . $source_table->name . ' not found');
                }
                if (count($chunk) > 1000) {
                    Price::upsert($chunk, ['security_id', 'date']);
                    $chunk = [];
                }
            }
            Price::upsert($chunk, ['security_id', 'date']);
            if (Price::sourceTableFilter($source_table->name)->max('source_last_updated') > $newest_price_updated) {
                $this->update_momentum = TRUE;
            }
        }

        \Log::info('SHARADAR price data successfully updated from Quandl.');
    }

    private function updateFredPrices() {
        $fed_debt_table = SourceTable::firstOrCreate(['name' => 'GFDEBTN']);
        $usd = Currency::firstOrCreate(['code' => 'USD']);
        $fed_debt_security = Security::firstOrCreate([
            'source_table_id' => $fed_debt_table->id,
            'name' => 'U.S. Federal Debt',
        ], [
            'is_delisted' => FALSE,
            'currency_id' => $usd->id,
            'scale_marketcap' => 0,
            'scale_revenue' => 0,
        ]);

        $url = 'https://www.quandl.com/api/v3/datasets/FRED/' . $fed_debt_table->name . '.csv';

        $lines = $this->fetchQuandlCSV($url, $params = []);
        $header = str_getcsv(array_shift($lines));
        $prices = [];
        foreach ($lines as $line) {
            $line = array_combine($header, str_getcsv($line));
            $prices[] = [
                'security_id' => $fed_debt_security->id,
                'date' => $line['Date'],
                'close' => $line['Value'] * 1000000,
                'close_unadj' => $line['Value'] * 1000000,
            ];
        }
        Price::upsert($prices, ['security_id', 'date']);
        \Log::info('FRED price data successfully updated from Quandl.');
    }

    private function updateLondonPrices() {
        $usd = Currency::firstOrCreate(['code' => 'USD']);
        foreach([
            'GOLD' => [
                'name' => 'LBMA Gold Prices',
                'source' => 'LBMA',
                'close_column' => 'USD (PM)',
            ],
            'SILVER' => [
                'name' => 'LBMA Silver Prices',
                'source' => 'LBMA',
                'close_column' => 'USD',
            ],
            'PALL' => [
                'name' => 'LBMA Palladium Prices',
                'source' => 'LPPM',
                'close_column' => 'USD PM',
            ],
            'PLAT' => [
                'name' => 'LBMA Platinum Prices',
                'source' => 'LPPM',
                'close_column' => 'USD PM',
            ],
        ] as $source_table_name => $security_data) {
            $source_table = SourceTable::firstOrCreate(['name' => $source_table_name]);
            $url = 'https://www.quandl.com/api/v3/datasets/' . $security_data['source'] . '/' . $source_table_name . '.csv';
            $params = [];

            if ($this->argument('start_date')) {
                $params['start_date'] = $this->argument('start_date');
            } else {
                $params['start_date'] = Price::sourceTableFilter($source_table->name)->max('source_last_updated');
            }
            if ($this->argument('end_date')) {
                $params['end_date'] = $this->argument('end_date');
            }

            $lines = $this->fetchQuandlCSV($url, $params);
            $header = str_getcsv(array_shift($lines));

            $chunk = [];
            foreach ($lines as $line) {
                $line = array_combine($header, str_getcsv($line));
                $security = Security::firstOrCreate([
                    'source_table_id' => $source_table->id,
                    'name' => $security_data['name'],
                ], [
                    'is_delisted' => FALSE,
                    'currency_id' => $usd->id,
                    'scale_marketcap' => 0,
                    'scale_revenue' => 0,
                ]);

                $price = [
                    'security_id' => $security->id,
                    'date' => $line['Date'],
                    'close' => $line[$security_data['close_column']],
                ];

                if ($price['close'] == '') {
                    continue;
                }
                $chunk[] = $price;

                if (count($chunk) > 1000) {
                    Price::upsert($chunk, ['security_id', 'date']);
                    $chunk = [];
                }
            }
            Price::upsert($chunk, ['security_id', 'date']);
        }
        \Log::info('LBMA price data successfully updated from Quandl.');
    }

    private function updateRecessions() {
        $url = 'https://www.quandl.com/api/v3/datasets/FRED/USREC.csv';

        $lines = $this->fetchQuandlCSV($url, $params = []);
        $header = str_getcsv(array_shift($lines));
        $recessions = [];
        foreach ($lines as $line) {
            $line = array_combine($header, str_getcsv($line));
            $recessions[] = [
                'date' => $line['Date'],
                'is_recession' => boolval(floatval($line['Value'])),
            ];
        }
        \DB::table('recessions')->upsert($recessions, ['date']);
        \Log::info('FRED recession data successfully updated from Quandl.');
    }

}
