<?php

namespace App\Exports;

use App\Wallet;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AllWallets implements FromView
{
    public function view(): View
    {
        $wallets = Wallet::orderBy('current_balance', 'desc')->get();
        return view('exports.wallets', [
            'wallets' => $wallets,
        ]);
    }
}
