<?php

namespace App\Exports;

use App\LoanRequest;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LoanRequests implements FromView
{
    public function view(): View
    {
        $loanRequests = LoanRequest::where('approval_status','PENDING')->orderBy('id','desc')->get();
        return view('exports.loans', [
            'loanRequests' => $loanRequests,
        ]);
    }
}
