@extends('layouts.app')
@section('title', 'Managed Customers')
@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#customers-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('managed-customers-dt', $filter) }}', // the route to be called via ajax
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
                        <h4 class="card-title">Managed Customers</h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">
                            @if(auth()->user()->role->has_perm([44]))
                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#new-customer-modal">
                                    <i class="fa fa-plus"></i> Create new Customer
                                </button>
                            @endif
                        </div>

                        @include('layouts.common.success')
                        @include('layouts.common.warning')
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


{{--    modals--}}
    <div class="modal fade" id="new-customer-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> Add New Customer</h4>
                </div>
                <div class="modal-body" >

                    <form action="{{ url('customers/managed/new') }}"  method="get">
                        {{ csrf_field() }}

                        <div class="form-group mb-4">
                            <label class="control-label" for="id_no">ID/Passport Number</label>
                            <input type="text" value="{{ old('id_no') }}" class="form-control" id="id_no" name="id_no" required/>
                        </div>


                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Continue</button>
                            {{--<!-- {!! $actionsRepo->formButtons() !!} -->--}}
                        </div>
                    </form>

                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>


@endsection
