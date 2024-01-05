<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Product</th>
        <th>Amount Repaid</th>
        <th>Balance</th>
        <th>Channel</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    @foreach($loanRepayments as $loanRepayment)
        <tr>
            <td>{{ $loanRepayment->id }}</td>
            <td>{{ optional(optional($loanRepayment->loan_request)->product)->name }}</td>
            <td>{{ optional(optional($loanRepayment->loan_request)->user)->name.' '.optional(optional($loanRepayment->loan_request)->user)->surname }}</td>
            <td>{{ optional((optional($loanRepayment->loan_request)->user)->wallet)->currency.' '. number_format($loanRepayment->amount_repaid) }}</td>
            <td>{{optional((optional($loanRepayment->loan_request)->user)->wallet)->currency.' '. number_format($loanRepayment->outstanding_balance) }}</td>
            <td>{{ $loanRepayment->payment_channel }}</td>
            <td>{{ $loanRepayment->description }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
