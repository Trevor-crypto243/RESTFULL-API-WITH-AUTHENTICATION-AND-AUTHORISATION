@extends('layouts.app')
@section('title', 'Loan Products')
@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#products-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('loan-products-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    // {data: 'interest_rate', name: 'interest_rate'},
                    {data: 'max_period_months', name: 'max_period_months'},
                    {data: 'fee_application', name: 'fee_application'},
                    {data: 'description', name: 'description'},
                    {data: 'created_at', name: 'created_at'},
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
                    searchPlaceholder: "Search Products",
                },
                "order": [[0, "desc"]]
            });
            // live search
            var _ModalTitle = $('#user-modal-title'),
                _SpoofInput = $('#user-spoof-input'),
                _Form = $('#user-form');
            //add
            $(document).on('click', '.add-btn', function() {
                _ModalTitle.text('Add');
                _SpoofInput.val('POST');
                // $('#name').val('');
                // $('#detail').val('');
                // $('#id').val('');
                $('#user-modal').modal('show');
            });
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
                        <h4 class="card-title">Loan Products</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-btn">
                                Create Product
                            </button>
                        </div>
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="products-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
{{--                                        <th>Interest</th>--}}
                                        <th>Max Period</th>
                                        <th>Fee Applied</th>
                                        <th>Description</th>
                                        <th>Date Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
{{--                                        <th>Interest</th>--}}
                                        <th>Max Period</th>
                                        <th>Fee Applied</th>
                                        <th>Description</th>
                                        <th>Date Created</th>
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
    <div class="modal fade" id="user-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"> Send Bulk SMS to user group</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('products/loans') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="name">Product Name</label>
                                    <input type="text" value="{{ old('name') }}" class="form-control" id="name" name="name" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="max_period_months">Maximum period(Months)</label>
                                    <input type="number" value="{{ old('max_period_months') }}" class="form-control" id="max_period_months" name="max_period_months" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="max_period_months">Monthly Interest Rate</label>
                                    <input type="number" value="{{ old('interest_rate') }}" class="form-control" id="interest_rate" name="interest_rate" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <select class="selectpicker" data-style="select-with-transition" title="Other Fees Application" tabindex="-98"
                                            name="fee_application" id="fee_application" required>
                                        <option value="BEFORE DISBURSEMENT">BEFORE DISBURSEMENT</option>
                                        <option value="AFTER DISBURSEMENT">AFTER DISBURSEMENT</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="min_amount">Minimum Amount</label>
                                    <input type="number" value="{{ old('min_amount') }}" class="form-control" id="min_amount" name="min_amount" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="max_amount">Maximum Amount</label>
                                    <input type="number" value="{{ old('max_amount') }}" class="form-control" id="max_amount" name="max_amount" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="message">Description</label>
                                    <textarea name="description" rows="3" id="description" class="form-control" required></textarea>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close"></i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="fa fa-save"></i> Save</button>
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
