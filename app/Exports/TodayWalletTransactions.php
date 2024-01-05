<?php

namespace App\Exports;

use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TodayWalletTransactions implements FromView
{
    public function view(): View
    {
        $transactions = WalletTransaction::whereDate('created_at',Carbon::now())
            ->orderBy('id', 'desc')
            ->get();
        return view('exports.wallet_transactions', [
            'transactions' => $transactions,
        ]);
    }
}
