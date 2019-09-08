<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
        $this->updateSecurities();
        $this->updatePrices();
        $this->updateActions();
    }

    private function updateSecurities() {
        // get link to bulk download file
        $curl = curl_init();
        $url = 'https://www.quandl.com/api/v3/datatables/SHARADAR/TICKERS';
        $url .= '?api_key=' . env('QUANDL_KEY');
        $url .= '&qopts.export=true';

        if ($this->argument('start_date')) {
            $url .= '&lastupdated.gte=' . $this->argument('start_date');
        } else {
            $url .= '&lastupdated.gte=' . Security::max('source_last_updated');
        }
        if ($this->argument('end_date')) {
            $url .= '&lastupdated.lte=' . $this->argument('end_date');
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $results = json_decode(curl_exec($curl), TRUE);
        $bulk_link = $results['datatable_bulk_download']['file']['link'];
        curl_close($curl);

        // save bulk download file
        $curl = curl_init();
        $zip_filename = tempnam(sys_get_temp_dir(), 'quandl_securities_');
        $zip_file = fopen($zip_filename, 'w');
        curl_setopt($curl, CURLOPT_URL, $bulk_link);
        curl_setopt($curl, CURLOPT_FILE, $zip_file);
        $results = curl_exec($curl);
        curl_close($curl);
        fclose($zip_file);

        // read bulk download file
        $zip = new ZipArchive();
        $file = $zip->open($zip_filename);
        $lines = explode(PHP_EOL, $zip->getFromIndex(0));
        // delete header row
        array_shift($lines);
        $zip->close();
        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }
            $line = explode(',', $line);
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
                    'source_id' => $line[1],
                ],
                [
                    'source_table_id' => $source_table_id,
                    'ticker' => $line[2],
                    'name' => $line[3],
                    'exchange_id' => $exchange_id,
                    'is_delisted' => $line[5],
                    'category_id' => $category_id,
                    'sic_industry_id' => $sic_industry_id,
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
        }

        \Log::info('Security data successfully updated from Quandl.');
    }

    public function updatePrices() {
        // get link to bulk download file
        $curl = curl_init();
        $url = 'https://www.quandl.com/api/v3/datatables/SHARADAR/SEP';
        $url .= '?api_key=' . env('QUANDL_KEY');
        $url .= '&qopts.export=true';

        if ($this->argument('start_date')) {
            $url .= '&lastupdated.gte=' . $this->argument('start_date');
        } else {
            $url .= '&lastupdated.gte=' . Price::max('source_last_updated');
        }
        if ($this->argument('end_date')) {
            $url .= '&lastupdated.lte=' . $this->argument('end_date');
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $results = json_decode(curl_exec($curl), TRUE);
        $bulk_link = $results['datatable_bulk_download']['file']['link'];
        curl_close($curl);

        // save bulk download file
        $curl = curl_init();
        $zip_filename = tempnam(sys_get_temp_dir(), 'quandl_prices_');
        $zip_file = fopen($zip_filename, 'w');
        curl_setopt($curl, CURLOPT_URL, $bulk_link);
        curl_setopt($curl, CURLOPT_FILE, $zip_file);
        $results = curl_exec($curl);
        curl_close($curl);
        fclose($zip_file);

        // read bulk download file
        $zip = new ZipArchive();
        $file = $zip->open($zip_filename);
        $lines = explode(PHP_EOL, $zip->getFromIndex(0));
        // delete header row
        array_shift($lines);
        $zip->close();
        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }
            $line = explode(',', $line);
            $security = Security::where('ticker', $line[0])->first();
            if ($security) {
                $price = Price::updateOrCreate(
                    [
                        'security_id' => $security->id,
                        'date' => $line[1],
                    ],
                    [
                        'open' => $line[2],
                        'high' => $line[3],
                        'low' => $line[4],
                        'close' => $line[5],
                        'volume' => $line[6],
                        'dividends' => $line[7],
                        'close_unadj' => $line[8],
                        'source_last_updated' => $line[9],
                    ]
                );
            }
        }
        \Log::info('Price data successfully updated from Quandl.');
    }

    public function updateActions() {
        // get link to bulk download file
        $curl = curl_init();
        $url = 'https://www.quandl.com/api/v3/datatables/SHARADAR/ACTIONS';
        $url .= '?api_key=' . env('QUANDL_KEY');
        $url .= '&qopts.export=true';

        if ($this->argument('start_date')) {
            $url .= '&date.gte=' . $this->argument('start_date');
        } else {
            $url .= '&date.gte=' . \DB::table('action_security')->max('date');
        }
        if ($this->argument('end_date')) {
            $url .= '&date.lte=' . $this->argument('end_date');
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $results = json_decode(curl_exec($curl), TRUE);
        $bulk_link = $results['datatable_bulk_download']['file']['link'];
        curl_close($curl);

        // save bulk download file
        $curl = curl_init();
        $zip_filename = tempnam(sys_get_temp_dir(), 'quandl_actions_');
        $zip_file = fopen($zip_filename, 'w');
        curl_setopt($curl, CURLOPT_URL, $bulk_link);
        curl_setopt($curl, CURLOPT_FILE, $zip_file);
        $results = curl_exec($curl);
        curl_close($curl);
        fclose($zip_file);

        // read bulk download file
        $zip = new ZipArchive();
        $file = $zip->open($zip_filename);
        $lines = explode(PHP_EOL, $zip->getFromIndex(0));
        // delete header row
        array_shift($lines);
        $zip->close();
        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }
            $line = explode(',', $line);
            $action = Action::firstOrCreate(['name' => $line[1]]);
            $security = Security::where('ticker', $line[2])->first();
            if ($security) {
                $action->securities()->attach($security->id, [
                    'date' => $line[0],
                    'value' => $line[4],
                ]);
            }
        }
        \Log::info('Action data successfully updated from Quandl.');
    }
}