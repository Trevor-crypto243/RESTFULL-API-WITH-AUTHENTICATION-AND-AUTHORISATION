<?php

namespace App\Exports;

use App\LoanRequest;
use App\LoanSchedule;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LoansRejected implements FromView
{
    public function view(): View
    {
        $loanRequests = LoanRequest::where('approval_status','REJECTED')->orderBy('id','desc')->get();


        return view('exports.loans', [
            'loanRequests' => $loanRequests,
        ]);
    }
}
