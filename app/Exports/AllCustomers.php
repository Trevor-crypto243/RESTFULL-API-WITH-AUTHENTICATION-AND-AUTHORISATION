<?php

namespace App\Exports;

use App\CustomerProfile;

use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AllCustomers implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {

        $customers = CustomerProfile::all();

        return view('exports.customers', [
            'customers' => $customers,
        ]);
    }
}
