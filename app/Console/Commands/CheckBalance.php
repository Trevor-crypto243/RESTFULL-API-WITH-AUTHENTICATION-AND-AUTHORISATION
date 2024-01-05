<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balance:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check B2C balance';

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
        $ENDPOINT = 'http://pay.localhost/orgbalance.php';
        $headers = array(
            'Content-type: application/json',
        );


        $payload = array(
            "shortcode"=>"3028315"
        );

        $body = json_encode($payload, JSON_PRETTY_PRINT);

        //dd($encBody);

        Log::info("Querying b2c balance...");

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $ENDPOINT); // point to endpoint
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_STDERR, $fp);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  //data
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);// request time out
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0'); // no ssl verifictaion
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');


        $result=curl_exec($ch);
        curl_close($ch);

        Log::info("See response below...");
        Log::info($result);
    }
}
