<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Security;
use App\Models\Price;
use ZipArchive;

class UpdateSecurities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'securities:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull the latest prices for securities from Quandl';

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

        // get link to bulk download file
        $curl = curl_init();
        $url = 'https://www.quandl.com/api/v3/datatables/SHARADAR/SEP';
        $url .= '?api_key=' . env('QUANDL_KEY');
        $url .= '&qopts.export=true';
        $url .= '&lastupdated.gte=' . Security::max('source_last_updated');
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $results = json_decode(curl_exec($curl), TRUE);
        $bulk_link = $results['datatable_bulk_download']['file']['link'];
        curl_close($curl);

        // save bulk download file
        $curl = curl_init();
        $zip_filename = tempnam(sys_get_temp_dir(), 'quandl_');
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
        \Log::info('Security data successfully updated from Quandl.');
    }
}
