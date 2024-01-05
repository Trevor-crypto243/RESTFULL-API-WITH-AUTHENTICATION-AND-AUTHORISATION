<?php

namespace App\Console\Commands;

use App\Alert;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HourlyBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check ad send hourly balance to subscribers';

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
     * @return int
     */
    public function handle()
    {
        $alerts = Alert::where('type','HOURLY_BALANCE')->get();

        $message = "Paybill balance as at ".
            Carbon::now()->isoFormat('MMMM Do YYYY, h:mm:ss a')." Utility: Ksh. ".
            number_format(optional(\App\PaybillBalance::where('shortcode','3028315')->first())->utility, 2). ". MMF: Ksh. ".number_format(optional(\App\PaybillBalance::where('shortcode','3028315')->first())->mmf, 2);

        foreach ($alerts as $alert){
            send_sms($alert->recipient, $message);
        }

    }
}
