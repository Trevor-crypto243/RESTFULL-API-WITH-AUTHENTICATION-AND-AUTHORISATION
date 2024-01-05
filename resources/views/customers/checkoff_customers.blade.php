@extends('layouts.app')
@section('title', 'Checkoff Customers')
@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#customers-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('checkoff-customers-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'surname', name: 'surname'},
                    {data: 'id_no', name: 'id_no'},
                    {data: 'phone_no', name: 'phone_no'},
                    // {data: 'max_limit', name: 'max_limit'},
                    {data: 'is_checkoff', name: 'is_checkoff'},
                    {data: 'status', name: 'status'},
                    {data: 'loans', name: 'loans'},
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
                    searchPlaceholder: "Search Customers",
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
                            <i class="material-icons">list</i>
                        </div>
                        <h4 class="card-title">Checkoff Customers</h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">
                            <a href="{{url('customers/checkoff/export')}}" class="btn btn-success btn-sm">
                                <span class="material-icons">file_download </span> Export Excel
                            </a>

                            <form id="filter-form" class="form-inline form-horizontal" action="{{ '/customers/search' }}" method="POST">
                                @csrf

                                <div class="form-group ">


                                    <div class="form-group text-left mb-2 mx-sm-3">
                                        <label class="control-label" for="id_no">ID Number</label>
                                        <input type="text" name="id_no" id="id-no" class="form-control"/>
                                    </div>


                                    <div class="form-group text-left mb-2 mx-sm-3">
                                        <label class="control-label" for="phone_no">Phone Number (254...)</label>
                                        <input type="number" name="phone_no" id="phone-no" class="form-control"/>
                                    </div>



                                </div>

                                <div class="form-group mb-2">
                                    <button type="submit" class="btn btn-success btn-sm"> Search</button>
                                </div>
                            </form>

                        </div>

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="customers-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>ID No.</th>
                                        <th>Phone No.</th>
{{--                                        <th>Max. Limit</th>--}}
                                        <th>Is Checkoff</th>
                                        <th>Status</th>
                                        <th>Loans</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>ID No.</th>
                                        <th>Phone No.</th>
{{--                                        <th>Max. Limit</th>--}}
                                        <th>Is Checkoff</th>
                                        <th>Status</th>
                                        <th>Loans</th>
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

@endsection
