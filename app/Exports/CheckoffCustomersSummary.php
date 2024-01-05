<?php

namespace App\Exports;

use App\Employer;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CheckoffCustomersSummary implements FromView
{
    public function view(): View
    {
        $checkoffSummary = Employer::get();

        return view('exports.checkoff_customers_summary', [
            'checkoffSummary' => $checkoffSummary,
        ]);
    }
}
