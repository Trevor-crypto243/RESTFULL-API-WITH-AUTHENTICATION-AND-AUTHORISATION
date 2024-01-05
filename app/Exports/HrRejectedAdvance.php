<?php

namespace App\Exports;

use App\AdvanceApplication;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class HrRejectedAdvance implements FromView
{
    private $employerId;

    public function __construct(int $employerId)
    {
        $this->employerId = $employerId;
    }


    public function view(): View
    {
        $applications = AdvanceApplication::where('employer_id',$this->employerId)->where('hr_status','REJECTED')->get();

        return view('exports.hr_inua', [
            'applications' => $applications,
            'empId' => $this->employerId
        ]);
    }
}
