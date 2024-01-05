<?php

namespace App\Exports;

use App\LoanRequest;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AllLoans implements FromView
{
    public function view(): View
    {
        $loanRequests = LoanRequest::orderBy('id','desc')->get();
        return view('exports.loans', [
            'loanRequests' => $loanRequests,
        ]);
    }
}
