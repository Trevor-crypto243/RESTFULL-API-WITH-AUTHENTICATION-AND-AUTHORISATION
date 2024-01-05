@extends('layouts.app')
@section('title', 'Banks/Branches')
@push('js')
    <script>

        $(function() {
            // server side - lazy loading
            $('#banks-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('banks-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'swift_code', name: 'swift_code'},
                    {data: 'bank_name', name: 'bank_name'},
                    {data: 'branches', name: 'branches'},
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
                    searchPlaceholder: "Search Banks",
                },
                "order": [[0, "desc"]]
            });


            $('#branches-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('branches-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'branch_name', name: 'branch_name'},
                    {data: 'bank', name: 'bank'},
                    {data: 'sort_code', name: 'sort_code'},
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
                    searchPlaceholder: "Search Branches",
                },
                "order": [[0, "desc"]]
            });


            var _ModalTitle = $('#make-modal-title'),
                _SpoofInput = $('#make-spoof-input');

            //add bank
            $(document).on('click', '.add-bank-btn', function() {
                _ModalTitle.text('Add');
                _SpoofInput.val('POST');
                $('#bank_name').val('');
                $('#swift_code').val('');
                $('#id').val('');
                $('#bank-modal').modal('show');
            });
            // edit bank
            $(document).on('click', '.edit-bank-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id'),
                    _Form = $('#makeform');

                if (_id !== '') {
                    $.ajax({
                        url: _Btn.attr('source'),
                        type: 'get',
                        dataType: 'json',
                        beforeSend: function() {
                            _ModalTitle.text('Edit');
                            _SpoofInput.removeAttr('disabled');
                        },
                        success: function(data) {
                            console.log(data);
                            // populate the modal fields using data from the server
                            $('#bank_name').val(data['bank_name']);
                            $('#swift_code').val(data['swift_code']);
                            $('#id').val(data['id']);

                            // set the update url
                            var action =  _Form .attr('action');
                            // action = action + '/' + season_id;
                            console.log(action);
                            _Form .attr('action', action);

                            // open the modal
                            $('#bank-modal').modal('show');
                        }
                    });
                }
            });




            var _modelModalTitle = $('#model-modal-title'),
                _modelSpoofInput = $('#model-spoof-input');
            //add branch
            $(document).on('click', '.add-branch-btn', function() {
                _modelModalTitle.text('Add');
                _modelSpoofInput.val('POST');
                $('#bank_id').val('').change();
                $('#branch_id').val('');
                $('#sort_code').val('');
                $('#branch_name').val('');
                $('#branches-modal').modal('show');
            });

            // edit model
            $(document).on('click', '.edit-branch-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id'),
                    _Form = $('#modelform');

                if (_id !== '') {
                    $.ajax({
                        url: _Btn.attr('source'),
                        type: 'get',
                        dataType: 'json',
                        beforeSend: function() {
                            _modelModalTitle.text('Edit');
                            _modelSpoofInput.removeAttr('disabled');
                        },
                        success: function(data) {
                            console.log(data);
                            // populate the modal fields using data from the server
                            $('#bank_id').val(data['bank_id']).change();
                            $('#branch_name').val(data['branch_name']);
                            $('#sort_code').val(data['sort_code']);
                            $('#branch_id').val(data['id']);

                            // set the update url
                            var action =  _Form .attr('action');
                            // action = action + '/' + season_id;
                            console.log(action);
                            _Form .attr('action', action);

                            // open the modal
                            $('#branches-modal').modal('show');
                        }
                    });
                }
            });

        });


        $('.delete-model-form').on('submit', function() {
            if (confirm('Are you sure you want to delete this vehicle model?')) {
                return true;
            }
            return false;
        });

    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                @include('layouts.common.success')
                @include('layouts.common.warnings')
                @include('layouts.common.warning')
            </div>
        </div>
        <div class="row">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">list</i>
                        </div>
                        <h4 class="card-title">Banks</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-bank-btn">
                                Add Bank
                            </button>
                        </div>

                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="banks-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Swift Code</th>
                                        <th>Bank Name</th>
                                        <th>Branches</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Swift Code</th>
                                        <th>Bank Name</th>
                                        <th>Branches</th>
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

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">list</i>
                        </div>
                        <h4 class="card-title">Bank Branches</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-branch-btn">
                                Add Bank Branch
                            </button>
                        </div>

                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="branches-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Branch</th>
                                        <th>Bank</th>
                                        <th>SortCode</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Branch</th>
                                        <th>Bank</th>
                                        <th>SortCode</th>
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
    <div class="modal fade" id="bank-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"><span id="make-modal-title">Add</span> Bank</h4>
                </div>
                <div class="modal-body" >
                    <form id="makeform" action="{{ url('bank/banks') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="make-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="make">Bank Name</label>
                                    <input type="text" value="{{ old('bank_name') }}" class="form-control" id="bank_name" name="bank_name" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="make">Swift Code</label>
                                    <input type="text" value="{{ old('swift_code') }}" class="form-control" id="swift_code" name="swift_code" required />
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

    <div class="modal fade" id="branches-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"><span id="model-modal-title">Add</span> Bank Branch</h4>
                </div>
                <div class="modal-body" >
                    <form id="modelform" action="{{ url('bank/branches') }}" method="post" id="model-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="model-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Bank" tabindex="-98"
                                            name="bank_id" id="bank_id" required>
                                        @foreach($banks as $bank)
                                            <option value="{{$bank->id}}">{{$bank->bank_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="sort_code">Sort Code</label>
                                    <input type="text" value="{{ old('sort_code') }}" class="form-control" id="sort_code" name="sort_code" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="sort_code">Branch Name</label>
                                    <input type="text" value="{{ old('branch_name') }}" class="form-control" id="branch_name" name="branch_name" required />
                                </div>
                            </div>
                        </div>






                        <input type="hidden" name="branch_id" id="branch_id"/>
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
