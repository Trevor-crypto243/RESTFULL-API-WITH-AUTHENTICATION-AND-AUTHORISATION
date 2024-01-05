@extends('layouts.app')
@section('title', 'Leads')
@push('js')
    <script>
        $(function() {
            $('#leads-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('leads-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'id_no', name: 'id_no'},
                    {data: 'msisdn', name: 'phone_no'}
                ],
                "pagingType": "full_numbers",
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search Leads",
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
                        <h4 class="card-title">Leads</h4>
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
                                        <input type="number" name="msisdn" id="phone-no" class="form-control"/>
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
                            <table id="leads-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>ID No.</th>
                                        <th>Phone No.</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>ID No.</th>
                                        <th>Phone No.</th>
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
