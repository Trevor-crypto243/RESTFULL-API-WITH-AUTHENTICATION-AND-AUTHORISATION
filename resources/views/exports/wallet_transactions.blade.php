<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Amount</th>
        <th>Prev. Bal</th>
        <th>Type</th>
        <th>Source</th>
        <th>TRX. ID</th>
        <th>Date</th>
        <th>Narration</th>
    </tr>
    </thead>
    <tbody>
    @foreach($transactions as $transaction)
        <tr>
            @php
                $user = \App\User::where('wallet_id',$transaction->wallet_id)->first();
                $name = '';

                   if (is_null($user)){
                       //get for company
                       $company = \App\Company::where('wallet_id',$transaction->wallet_id)->first();

                       $name = is_null($company) ? "" : $company->business_name;

                   }else{
                      $name =  $user->surname.' '.$user->name;
                   }

            @endphp

            <td>{{ $transaction->id }}</td>
            <td>{{ $name }}</td>
            <td>{{ $transaction->amount }}</td>
            <td>{{ number_format($transaction->previous_balance) }}</td>
            <td>{{ $transaction->transaction_type == "CR" ? 'CREDIT' : 'DEBIT' }}</td>
            <td>{{ $transaction->source }}</td>
            <td>{{ $transaction->trx_id }}</td>
            <td>{{ \Carbon\Carbon::parse($transaction->created_at)->isoFormat('MMM Do YYYY H:m:s') }}</td>
            <td>{{ $transaction->narration }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
