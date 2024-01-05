<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Product</th>
        <th>Amount</th>
        <th>Period</th>
        <th>Approval</th>
        <th>Repayment</th>
    </tr>
    </thead>
    <tbody>
    @foreach($loanRequests as $loanRequest)
        <tr>
            <td>{{ $loanRequest->id }}</td>
            <td>{{ optional($loanRequest->user)->surname.' '.optional($loanRequest->user)->name }}</td>
            <td>{{ optional($loanRequest->product)->name }}</td>
            <td>{{ optional(optional($loanRequest->user)->wallet)->currency.' '. number_format($loanRequest->amount_requested) }}</td>
            <td>{{ $loanRequest->period_in_months.' Months' }}</td>
            <td>{{ $loanRequest->approval_status }}</td>
            <td>{{ $loanRequest->repayment_status }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
