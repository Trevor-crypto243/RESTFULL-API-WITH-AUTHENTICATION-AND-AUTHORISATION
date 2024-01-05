@extends('layouts.app')
@section('title', 'Logbook Loan Applications')
@push('js')
    <script>

        var applicationsDt = $('#applications-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax: $('#applications-dt').data('source'), // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'client', name: 'client'},
                {data: 'applicant_type', name: 'applicant_type'},
                {data: 'status', name: 'status'},
                {data: 'requested_amount', name: 'requested_amount'},
                {data: 'vehicles', name: 'vehicles'},
                {data: 'payment_period', name: 'payment_period'},
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
                searchPlaceholder: "Search Applications",
            },
            "order": [[0, "desc"]]
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this),
                status = $('#status').val(),
                action = form.attr('action'),
                dt_url = action + status;
            // console.log(action);
            // console.log(dt_url);
            applicationsDt.ajax.url(dt_url).load();

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
                            <i class="material-icons">directions_car</i>
                        </div>
                        <h4 class="card-title">Logbook Applications. <small>All Logbook applications, filter by status</small></h4>
                    </div>
                    <div class="card-body">

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <div class="row w-100 justify-content-between">

                        <div class="">

                        <form id="filter-form" class="form-inline form-horizontal" action="{{ '/ajax/auto/applications/' }}" method="GET">
                            @csrf
                            <div class="form-group text-left mb-2 mx-sm-3">
                                <select class="selectpicker" data-style="select-with-transition" title="Select Status" tabindex="-98"
                                        name="status" id="status" required>
                                    <option value="NEW">NEW</option>
                                    <option value="IN REVIEW">IN REVIEW</option>
                                    <option value="AMENDMENT">AMENDMENT</option>
                                    <option value="OFFER">OFFER</option>
                                    <option value="ACTIVE">ACTIVE</option>
                                    <option value="REJECTED">REJECTED</option>
                                    <option value="PAID">PAID</option>
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <button type="submit" class="btn btn-success btn-sm"> Filter</button>
                            </div>

                        </form>
                        </div>


                        <div class="mt-2    ">
                             <a class="dropdown-item btn btn-success btn-sm" href="{{ url('/auto/add-applicant') }}">Add Applicant</a>
                        </div>
                        </div>
                        

                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="applications-dt" data-source="{{ url('ajax/auto/applications/'.$status) }}" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Applicant</th>
                                    <th>Applicant Type</th>
                                    <th>Status</th>
                                    <th>Requested Amount</th>
                                    <th>Vehicles</th>
                                    <th>Period</th>
                                    <th>Date Applied</th>
                                    <th>Action</th>
                                </tr>
                                </thead>                         
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Applicant</th>
                                    <th>Applicant Type</th>
                                    <th>Status</th>
                                    <th>Requested Amount</th>
                                    <th>Vehicles</th>
                                    <th>Period</th>
                                    <th>Date Applied</th>
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
