@extends('layouts.app')
@section('title', 'Checkoff Customer Summary')
@push('js')
    <script>

        $(function() {
            // server side - lazy loading
            $('#summary-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('checkoff-customers-summary-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'business_logo', name: 'business_logo'},
                    {data: 'business_name', name: 'business_name'},
                    {data: 'business_address', name: 'business_address'},
                    {data: 'business_phone_no', name: 'business_phone_no'},
                    {data: 'total_employees', name: 'total_employees'},
                    {data: 'actions', name: 'actions'},
                ],
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
                    searchPlaceholder: "Search Summary",
                },
                "order": [[0, "desc"]]
            });
            // live search
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
                            <i class="material-icons">work</i>
                        </div>
                        <h4 class="card-title">Checkoff Customer Summary</h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">
                            <a href="{{url('customers/checkoff/summary/export')}}" class="btn btn-success btn-sm">
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
                            <table id="summary-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Phone No.</th>
                                        <th>Employees</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Phone No.</th>
                                        <th>Employees</th>
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
