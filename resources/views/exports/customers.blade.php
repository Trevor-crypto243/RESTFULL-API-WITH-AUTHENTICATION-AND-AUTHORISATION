<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Surname</th>
        <th>ID No.</th>
        <th>Phone No.</th>
        <th>Is Checkoff</th>
        <th>Status</th>
        <th>Loans</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->id }}</td>
            <td>{{ optional($customer->user)->name }}</td>
            <td>{{ optional($customer->user)->surname }}</td>
            <td>{{ optional($customer->user)->id_no }}</td>
            <td>{{ optional($customer->user)->phone_no }}</td>
            <td>{{ $customer->is_checkoff ? 'YES' : 'NO' }}</td>
            <td>{{ $customer->status }}</td>
            <td>{{ optional($customer->user)->loans->count() }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
