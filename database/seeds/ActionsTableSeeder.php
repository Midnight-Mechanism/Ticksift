<?php

use Illuminate\Database\Seeder;
use App\Models\Action;
use App\Models\Security;

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
        $file = fopen($filename,"r");

        $header = TRUE;
        $chunk = [];
        while (($line = fgetcsv($file)) !== FALSE) {
            if (!$header) {
                $action = Action::firstOrCreate(['name' => $line[1]]);
                $security = Security::where('ticker', $line[2])->first();
                if (!$security) {
                    $this->command->error("Security with ticker " . $line[2] . " not found.");
                    continue;
                }
                $action->securities()->attach($security->id, [
                    'date' => $line[0],
                    'value' => $line[4],
                ]);
            } else {
                $header = FALSE;
            }
        }

        fclose($file);
    }
}
