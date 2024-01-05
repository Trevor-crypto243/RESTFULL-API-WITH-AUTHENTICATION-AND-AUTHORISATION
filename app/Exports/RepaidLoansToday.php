<?php

namespace App\Exports;

use App\LoanRepayment;
use App\LoanRequest;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RepaidLoansToday implements FromView
{
    public function view(): View
    {
        $loanRepayments = LoanRepayment::whereDate('created_at',Carbon::today())
            ->orderBy('id','desc')
            ->get();

        return view('exports.repaid_loans_today', [
            'loanRepayments' => $loanRepayments,
        ]);

    }
}
