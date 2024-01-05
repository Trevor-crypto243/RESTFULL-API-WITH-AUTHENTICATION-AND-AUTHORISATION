<?php

namespace App\Exports;

use App\LoanRequest;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LoansApproveToday implements FromView
{

    public function view(): View
    {
        $loanRequests = LoanRequest::whereDate('created_at',Carbon::now())->get();

        return view('exports.loans', [
            'loanRequests' => $loanRequests,
        ]);

    }
}
