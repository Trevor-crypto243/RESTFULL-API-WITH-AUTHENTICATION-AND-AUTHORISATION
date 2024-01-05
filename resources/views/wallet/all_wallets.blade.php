@extends('layouts.app')
@section('title', 'All Wallets')
@push('js')
    <script>


        var disburseDt = $('#transactions-dt').DataTable({
            processing: true, // loading icon
            serverSide: true, // this means the datatable is no longer client side
            ajax: '{{ route('all-wallets-dt') }}', // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'is_checkoff', name: 'is_checkoff'},
                {data: 'current_balance', name: 'current_balance'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
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
                searchPlaceholder: "Search Wallets",
            },
           // "order": [[2, "desc"]]
            ordering: false,
        });

    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">account_balance_wallet</i>
                        </div>
                        <h4 class="card-title">All Wallets</h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">
                            <a href="{{url('wallets/all/export')}}" class="btn btn-success btn-sm">
                                <span class="material-icons">file_download </span> Export Excel
                            </a>
                        </div>

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')


                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="transactions-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Is Checkoff</th>
                                    <th>Current Balance</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Is Checkoff</th>
                                    <th>Current Balance</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
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
