<?php

namespace App\Exports;

use App\LoanRequest;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RepaidLoans implements FromView
{

    public function view(): View
    {
        $loanRequests = LoanRequest::where('approval_status','APPROVED')
            ->whereIn('repayment_status',['PARTIALLY_PAID','PAID'])
            ->orderBy('id','desc')
            ->get();

        return view('exports.loans', [
            'loanRequests' => $loanRequests,
        ]);

    }
}
