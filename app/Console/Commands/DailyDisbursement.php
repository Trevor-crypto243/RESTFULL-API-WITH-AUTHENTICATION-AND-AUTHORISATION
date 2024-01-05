<?php

namespace App\Console\Commands;

use App\Alert;
use App\BulkDisbursement;
use App\Escrow;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DailyDisbursement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:dailydisbursement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A daily alert on how much was disbursed the previous day';

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
        $alerts = Alert::where('type','DAILY_DISBURSEMENT')->get();

        $yesterday = Carbon::now()->subDay();
        $amountYesterday = BulkDisbursement::where('status', 'SUCCEEDED')->whereDate('created_at', $yesterday)->sum('amount') ;
        $withdrawnYesterday = Escrow::where('status', 'SUCCEEDED')
            ->where('complete',true)
            ->whereDate('updated_at', $yesterday)
            ->sum('amount') ;


        $message = "Total amount disbursed via bulk yesterday (".
            Carbon::parse($yesterday)->isoFormat('MMM Do YYYY').") through M-PESA is Ksh. ".
            number_format($amountYesterday). ". Wallet withdrawals: Ksh. ".number_format($withdrawnYesterday);

        foreach ($alerts as $alert){
            send_sms($alert->recipient, $message);
        }

        $this->info('Daily disbursement alert have been sent');

    }
}
