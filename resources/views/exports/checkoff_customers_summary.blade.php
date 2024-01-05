<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>Phone No.</th>
        <th>Employees</th>

    </tr>
    </thead>
    <tbody>
    @foreach($checkoffSummary as $checkoff)
        <tr>
            <td>{{ $checkoff->id }}</td>
            <td>{{ $checkoff->business_name }}</td>
            <td>{{ $checkoff->business_address }}</td>
            <td>{{ $checkoff->business_phone_no }}</td>
            <td>{{ optional($checkoff->employees)->count() }}</td>

        </tr>
    @endforeach
    </tbody>
</table>
