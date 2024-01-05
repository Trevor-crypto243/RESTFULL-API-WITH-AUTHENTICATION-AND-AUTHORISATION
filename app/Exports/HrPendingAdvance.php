<?php

namespace App\Exports;

use App\Employee;
use App\AdvanceApplication;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class HrPendingAdvance implements FromView
{

    private $employerId;

    public function __construct(int $employerId)
    {
        $this->employerId = $employerId;
    }


    public function view(): View
    {
        $applications = AdvanceApplication::where('employer_id',$this->employerId)
            ->where('Quicksava_status','PROCESSING')
            ->where('hr_status','PENDING')
            ->get();

        return view('exports.hr_inua', [
            'applications' => $applications,
            'empId' => $this->employerId
        ]);
    }
}
