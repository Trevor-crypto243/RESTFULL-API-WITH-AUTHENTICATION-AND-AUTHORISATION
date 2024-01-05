<?php

namespace App\Exports;

use App\LoanRequest;
use App\LoanSchedule;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class LoansOverdue implements FromView
{
    public function view(): View
    {
        $loanSchedules = LoanSchedule::whereIn('status',['UNPAID','PARTIALLY_PAID'])
            ->whereDate('payment_date','<', Carbon::now())
            ->get();
        return view('exports.loan_schedules', [
            'loanSchedules' => $loanSchedules,
        ]);
    }
}
