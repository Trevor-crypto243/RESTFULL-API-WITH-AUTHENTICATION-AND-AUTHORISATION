@extends('layouts.app')
@section('title', 'Logbook Loan Applications')
@push('js')
    <script>

        var applicationsDt = $('#applications-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax:'{{ route('bank-accounts-dt') }}', // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'owner', name: 'owner'},
                {data: 'bank', name: 'bank'},
                {data: 'account_name', name: 'account_name'},
                {data: 'account_number', name: 'account_number'},
                {data: 'atm_url', name: 'atm_url'},
                {data: 'approved', name: 'approved'},
                {data: 'actions', name: 'actions'},
            ],
            // dom: 'Blfrtip',
            // buttons: [
            //     //'copy', 'excel', 'pdf',
            //     { "extend": 'copy', "text":'Copy Data',"className": 'btn btn-info btn-xs' },
            //     { "extend": 'excel', "text":'Export To Excel',"className": 'btn btn-sm btn-success' },
            //     { "extend": 'pdf', "text":'Export To PDF',"className": 'btn btn-default btn-xs' }
            // ],
            /*columnDefs: [
                {searchable: false, targets: [5]},
                {orderable: false, targets: [5]}
            ],*/
            "pagingType": "full_numbers",
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Accounts",
            },
            "order": [[0, "desc"]]
        });

        // $('.approve-account-form').on('submit', function() {
        //     if (confirm('Are you sure you want to APPROVE this bank account?')) {
        //         return true;
        //     }
        //     return false;
        // });
        //
        // $('.disapprove-account-form').on('submit', function() {
        //     if (confirm('Are you sure you want to DISAPPROVE this bank account?')) {
        //         return true;
        //     }
        //     return false;
        // });


    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">apartment</i>
                        </div>
                        <h4 class="card-title">Bank Accounts. <small>Review and approve/disapprove bank accounts</small></h4>
                    </div>
                    <div class="card-body">

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')


                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="applications-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Owner</th>
                                    <th>Bank</th>
                                    <th>Account Name</th>
                                    <th>Account Number</th>
                                    <th>ATM/Cheque</th>
                                    <th>Approved?</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Owner</th>
                                    <th>Bank</th>
                                    <th>Account Name</th>
                                    <th>Account Number</th>
                                    <th>ATM/Cheque</th>
                                    <th>Approved?</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <!-- end content-->
                </div>
                <!--  end card  -->
            </div>
            <!-- end col-md-12 -->
        </div>
        <!-- end row -->
    </div>



    {{--modal--}}




@endsection
