<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Product</th>
        <th>Date</th>
        <th>Scheduled Payment</th>
        <th>Status</th>
        <th>Amount Paid</th>
        <th>Amount Due</th>
    </tr>
    </thead>
    <tbody>
    @foreach($loanSchedules as $loanSchedule)
        <tr>
            <td>{{ $loanSchedule->id }}</td>
            <td>{{ optional(optional($loanSchedule->loan)->user)->surname.' '.optional(optional($loanSchedule->loan)->user)->name }}</td>
            <td>{{ optional(optional($loanSchedule->loan)->product)->name }}</td>
            <td>{{ $loanSchedule->payment_date }}</td>
            <td>{{ number_format($loanSchedule->scheduled_payment) }}</td>
            <td>{{ $loanSchedule->status }}</td>
            <td>{{ number_format($loanSchedule->actual_payment_done)  }}</td>
            <td>{{ number_format($loanSchedule->scheduled_payment - $loanSchedule->actual_payment_done,2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
