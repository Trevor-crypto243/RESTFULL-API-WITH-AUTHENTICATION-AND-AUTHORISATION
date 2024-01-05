@extends('layouts.datatables')
@section('title', 'My Profile')

@push('css')
    <style>
        .nav-link.active {
            background-color: #f44336 !important;
        }
    </style>
@endpush

@push('js')
    <script src="js/auth/my-profile.js"></script>
    <script>
        $(function() {
            // server side - lazy loading
            $('#accounts-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('accounts-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'alias', name: 'alias'},
                    {data: 'bank_branch_id', name: 'bank_branch_id'},
                    {data: 'account_name', name: 'account_name'},
                    {data: 'account_no', name: 'account_no'},
                    {data: 'actions', name: 'actions'}

                ],
                columnDefs: [
                    {searchable: false, targets: [5]},
                    {orderable: false, targets: [5]}
                ],
                "pagingType": "full_numbers",
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                "order": [[0, "desc"]]
            });

            $(document).on('submit', '.del_account_form', function () {
                if (confirm('Are you sure you want to delete the bank account ')) {
                    return true;
                }
                return false;
            });

            var accountModalTitle = $('#account-modal-title'),
                accSpoofInput = $('#acc-spoof-input'),
                accForm = $('#edit-account-form');

            // add bank account
            $('#add-bank-btn').on('click', function() {
                accountModalTitle.text('Add');
                accSpoofInput.attr('disabled', 'disabled');
                accForm.find('input.form-control, select').val('');
            });

            // edit bank account
            $(document).on('click', '.edit-account-btn', function() {
                var accountBtn = $(this);
                var account_id = accountBtn.attr('accnt-id');

                if (account_id != '') {
                    $.ajax({
                        url: accountBtn.attr('source'),
                        type: 'get',
                        dataType: 'json',
                        beforeSend: function() {
                            accountModalTitle.text('Edit');
                            accSpoofInput.removeAttr('disabled');
                        },
                        success: function(data) {
                            // populate the modal fields using data from the server
                            $('#alias').val(data['alias']);
                            $('#bank_branch_id').val(data['bank_branch_id']).trigger('change');
                            $('#account_name').val(data['account_name']);
                            $('#account_no').val(data['account_no']);
                            $('#id').val(data['id']);

                            // set the update url
                            var action = accForm.attr('action');
                            action = action + '/' + data['id'];
                            accForm.attr('action', action);

                            // open the modal
                            $('#accounts-modal').modal('show');
                        }
                    });
                }
            });

            // make bank branches a live search
            $('#bank_branch_id').select2();

            //auto login functionality
            $('#login_btn').click(function () {
                console.log('Calling login');
                $("#login_display").html("Please wait while data is loading. . .");
                var urlLogin = "http://mukwano.thinvoidcloud.com";

                $.post( urlLogin+"/dashboard/login.php",
                    {
                        username: $("#login_username").val(),
                        password: $("#login_password").val()
                    })
                    .done(function( data ) {
                        if(data.trim() == "success")
                        {
                            $("#login_message").html('Login success');
                            //location.href = urlLogin+"index.php";
                        }
                        else
                        {
                            $("#login_message").html(data);
                            $("#login_display").html("A problem occured processing the request");
                        }
                    });
            });
        });
    </script>
@endpush


@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-profile">
                    <div class="card-avatar">
                        <a href="#pablo">
                            <img class="img img-responsive" src="{{is_null( auth()->user()->photo) ?  $photo :  auth()->user()->photo }}">
                        </a>
                    </div>
                    <div class="card-body">

                        <!--tabbed content-->
                        <div class="row">
                            <div class="col-md-10 ml-auto mr-auto">
                                <div class="page-categories">
                                    <h3 class="title text-center">{{ $mfUser->surname . ' ' . $mfUser->first_name . ' ' . $mfUser->middle_name  }}</h3>
                                    <h4>{{$user->role->role_name}}</h4>
                                    <br />

                                    <ul class="nav nav-pills nav-pills-warning nav-pills-icons justify-content-center" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" tab-id="information" href="#information" role="tablist">
                                                <i class="material-icons">info</i> Information
                                            </a>
                                        </li>
                                        @if(isset($bank_accounts))
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" tab-id="banks" href="#bank-accounts" role="tablist">
                                                    <i class="material-icons">account_balance</i> Bank Accounts
                                                </a>
                                            </li>
                                        @endif
                                        @if(isset($withdrawals))
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" tab-id="banks" href="#withdrawals" role="tablist">
                                                    <i class="material-icons">money</i> Withdrawals
                                                </a>
                                            </li>
                                        @endif
                                        @if(isset($journal))
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" tab-id="banks" href="#my-statement" role="tablist">
                                                    <i class="material-icons">list_alt</i> My Statement
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                    <div class="tab-content tab-space tab-subcategories">
                                        <div class="tab-pane active" id="information">
                                            <div class="card text-left">
                                                <div class="card-header">
                                                    <h4 class="card-title">Profile Information</h4>
                                                    <p class="card-category">
                                                        This is your profile information.
                                                    </p>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <ul class="list-group">
                                                                <li class="list-group-item"><span class="text-muted">Name:</span> {{ $mfUser->surname . ' ' . $mfUser->first_name . ' ' . $mfUser->middle_name }}</li>
                                                                <li class="list-group-item"><span class="text-muted">Email:</span> {{ $user->email }}</li>
                                                                <li class="list-group-item"><span class="text-muted">Phone No:</span> {{ $user->mobile_no }}</li>
                                                            </ul>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <ul class="list-group">
                                                                <li class="list-group-item"><span class="text-muted">ID No:</span> {{ $mfUser->id_no }}</li>
                                                                <li class="list-group-item"><span class="text-muted">Gender:</span> {{ $mfUser->gender }}</li>
                                                                <li class="list-group-item"><span class="text-muted">DOB:</span> {{ $mfUser->dob }}</li>
                                                            </ul>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                        @if(isset($bank_accounts))
                                            <div class="tab-pane" id="bank-accounts">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h4 class="card-title">Manage your Bank Accounts</h4>
                                                        <p class="card-category">
                                                            These bank accounts are essential especially when making withdrawal requests
                                                        </p>
                                                    </div>
                                                    <div class="card-body">
                                                        @include('auth.partials.bank-accounts')
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if(isset($withdrawals))
                                            <div class="tab-pane" id="withdrawals">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h4 class="card-title">Withdrawals</h4>
                                                        <p class="card-category">
                                                            A list of your withdrawals
                                                        </p>
                                                    </div>
                                                    <div class="card-body">
                                                        @include('auth.partials.withdrawals')
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if(isset($journal))
                                            <div class="tab-pane" id="my-statement">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h4 class="card-title">My Statement</h4>
                                                    </div>
                                                    <div class="card-body">
                                                        @include('auth.partials.my-statement')
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end tabbed content-->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
