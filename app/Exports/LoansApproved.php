<?php

namespace App\Exports;

use App\LoanRequest;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LoansApproved implements FromView
{
    public function view(): View
    {
        $loanRequests = LoanRequest::where('approval_status','APPROVED')->orderBy('id','desc')->get();
        return view('exports.loans', [
            'loanRequests' => $loanRequests,
        ]);
    }
}
