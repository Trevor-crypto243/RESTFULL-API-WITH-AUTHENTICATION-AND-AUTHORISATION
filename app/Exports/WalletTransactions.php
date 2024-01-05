<?php

namespace App\Exports;

use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class WalletTransactions implements FromView
{
    public function view(): View
    {
        $transactions = WalletTransaction::orderBy('id', 'desc')->get();
        return view('exports.wallet_transactions', [
            'transactions' => $transactions,
        ]);
    }
}
