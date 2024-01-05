<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Surname</th>
        <th>Payroll No.</th>
        <th>Amount</th>
        <th>Installment</th>
        <th>Period</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    @foreach($applications as $application)
        @php
            $emp = \App\Employee::where('user_id', $application->user_id)->where('employer_id', $empId)->first();

          switch ($application->period_in_months) {
                    case 1:
                        $apr = 96;
                        break;
                    case 2:
                        $apr = 86.107;
                        break;
                    case 3:
                        $apr = 79.711;
                        break;

                    case 4:
                        $apr = 75.886;
                        break;

                    case 5:
                        $apr = 73.342;
                        break;

                    case 6:
                        $apr = 71.527;
                        break;

                    case 7:
                        $apr = 70.169;
                        break;

                    case 8:
                        $apr = 69.116;
                        break;

                    case 9:
                        $apr = 68.275;
                        break;

                    case 10:
                        $apr = 67.585;
                        break;

                    case 11:
                        $apr = 67.013;
                        break;

                    case 12:
                        $apr = 66.531;
                        break;
                    default:
                        $apr = 0.0;
                }

                $interestRate = $apr/12;

                $interestRatePercentage = $interestRate/100;
                $a = 1+$interestRatePercentage;
                $exponent = -1*$application->period_in_months;
                $raised = pow($a,$exponent);
                $raisedFormatted = sprintf("%f",$raised);
                $numerator = $application->amount_requested * $interestRatePercentage;
                $denominator = 1-$raisedFormatted;
                $monthlyTotalAmount = ceil($numerator/$denominator);

        @endphp
        <tr>
            <td>{{ $application->id }}</td>
            <td>{{ optional($application->user)->name }}</td>
            <td>{{ optional($application->user)->surname }}</td>
            <td>{{ optional($emp)->payroll_no }}</td>
            <td>{{ 'KES '. number_format($application->amount_requested) }}</td>
            <td>{{ 'KES '. number_format($monthlyTotalAmount) }}</td>
            <td>{{ $application->period_in_months. " Months" }}</td>
            <td>{{ $application->hr_status }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
