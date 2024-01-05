<?php

namespace App\Exports;

use App\LoanRequest;
use App\LoanSchedule;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class LoansDueToday implements FromView
{
    public function view(): View
    {
        $loanSchedules = LoanSchedule::whereIn('status',['UNPAID','PARTIALLY_PAID'])
            ->whereDate('payment_date', Carbon::now())
            ->get();

        return view('exports.loan_schedules', [
            'loanSchedules' => $loanSchedules,
        ]);

    }
}
