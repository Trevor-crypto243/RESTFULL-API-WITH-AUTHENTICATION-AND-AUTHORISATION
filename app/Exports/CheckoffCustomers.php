<?php

namespace App\Exports;

use App\CustomerProfile;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CheckoffCustomers implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {

        $customers = CustomerProfile::where('is_checkoff', true)->get();

        return view('exports.customers', [
            'customers' => $customers,
        ]);
    }
}
