<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Is Checkoff</th>
        <th>Current Balance</th>
        <th>Status</th>
        <th>Created At</th>
    </tr>
    </thead>
    <tbody>
    @foreach($wallets as $wallet)
        <tr>
            @php
                $user = \App\User::where('wallet_id',$wallet->id)->first();
                $name = '';
                $isCheckoff = '';

                   if (is_null($user)){
                       //get for company
                       $company = \App\Company::where('wallet_id',$wallet->id)->first();

                       $name = is_null($company) ? "" : $company->business_name;
                       $isCheckoff = 'NO';

                   }else{
                      $name =  $user->surname.' '.$user->name;

                      $custProfile = \App\CustomerProfile::where('user_id',$user->id)->first();

                      if (is_null($custProfile))
                          $isCheckoff = 'NO';
                      else
                          $isCheckoff = $custProfile->is_checkoff ? 'YES' : 'NO';
                   }

            @endphp

            <td>{{ $wallet->id }}</td>
            <td>{{ $name }}</td>
            <td>{{ $isCheckoff }}</td>
            <td>{{ 'KES'. number_format($wallet->current_balance,2) }}</td>
            <td>{{ $wallet->active ? 'ACTIVE' : 'FROZEN' }}</td>
            <td>{{ \Carbon\Carbon::parse($wallet->created_at)->isoFormat('MMM Do YYYY H:m:s') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
