@extends('layouts.app')
@section('title', 'advance Requests')
@push('js')
    <script>

        var disburseDt = $('#requests-dt').DataTable({
            processing: true, // loading icon
            serverSide: true, // this means the datatable is no longer client side
            ajax: '{{ route('user-advance-requests-dt',$user->id) }}', // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'employer', name: 'employer'},
                {data: 'payroll_no', name: 'payroll_no'},
                {data: 'loan_product', name: 'loan_product'},
                {data: 'amount_requested', name: 'amount_requested'},
                {data: 'period_in_months', name: 'period_in_months'},
                {data: 'quicksava_status', name: 'quicksava_status'},
                {data: 'hr_status', name: 'hr_status'},
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
                searchPlaceholder: "Search Requests",
            },
            "order": [[0, "desc"]]
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
                            <i class="material-icons">analytics</i>
                        </div>
                        <h4 class="card-title">{{$user->name}} - <small> advance Requests</small></h4>
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
                            <table id="requests-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employer</th>
                                    <th>Payroll No.</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>HR Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Employer</th>
                                    <th>Payroll No.</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>HR Status</th>
                                    <th>Date</th>
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




@endsection
