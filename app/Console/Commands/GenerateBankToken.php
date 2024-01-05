<?php

namespace App\Console\Commands;

use App\BankToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateBankToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banktoken:generate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Bank token';

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
        //get session ID
        $ENDPOINT = 'https://Quicksava.sidianbank.co.ke:9089/CreateSession';
        $headers = array(
            'Content-Type: application/json',
        );

//        $payload = array("CreateSessionRequest" => array("Username"=>"TEST_USER","Password"=>"0ffe1abd1a08215353c233d6e009613e95eec4253832a761af28ff37ac5a150c"));
        $payload = array("CreateSessionRequest" => array("Username"=>"Quicksava_USER","Password"=>"19ff9cf72e439c8014987041c64b628573cc2c48b6222dcb6e99ab72b81740f1"));

        $body = json_encode($payload, JSON_PRETTY_PRINT);

        info("Sending to endpoint...");
        info($ENDPOINT);

        info("payload...");
        info($body);


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $ENDPOINT); // point to endpoint
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_STDERR, $fp);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  //data
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);// request time out
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0'); // no ssl verifictaion
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '0');


        $result=curl_exec($ch);
        curl_close($ch);

        Log::info("Request sent. See response below...");
        Log::info($result);


        if ($result != null)
            $status = json_decode($result,true)['CreateSessionResponse']['Status'];
        else
            $status = "ERROR";


        if ($status == "SUCCESS"){
            $sessID = json_decode($result,true)['CreateSessionResponse']['sessionId'];

            $bt = new BankToken();
            $bt->token = $sessID;
            $bt->save();

        }else{

            if ($result != null)
                $error = json_decode($result,true)['CreateSessionResponse']['ErrorDescription'];
            else
                $error = "Bank not reachable. Please try again later";

            info("TOKEN ERROR: ".$error);
        }


        Log::info("end of request");

    }
}
