@extends('layouts.app')
@section('title', 'Vehicle Makes/Models')
@push('js')
    <script>

        $(function() {
            // server side - lazy loading
            $('#makes-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('makes-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'make', name: 'make'},
                    {data: 'models', name: 'models'},
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
                    searchPlaceholder: "Search Makes",
                },
                "order": [[0, "desc"]]
            });


            $('#models-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('models-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'model', name: 'model'},
                    {data: 'make', name: 'make'},
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
                    searchPlaceholder: "Search Models",
                },
                "order": [[0, "desc"]]
            });


            var _ModalTitle = $('#make-modal-title'),
                _SpoofInput = $('#make-spoof-input');

            //add make
            $(document).on('click', '.add-make-btn', function() {
                _ModalTitle.text('Add');
                _SpoofInput.val('POST');
                $('#make').val('');
                $('#id').val('');
                $('#make-modal').modal('show');
            });
            // edit make
            $(document).on('click', '.edit-make-btn', function() {
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
                            $('#make').val(data['make']);
                            $('#id').val(data['id']);

                            // set the update url
                            var action =  _Form .attr('action');
                            // action = action + '/' + season_id;
                            console.log(action);
                            _Form .attr('action', action);

                            // open the modal
                            $('#make-modal').modal('show');
                        }
                    });
                }
            });




            var _modelModalTitle = $('#model-modal-title'),
                _modelSpoofInput = $('#model-spoof-input');
            //add model
            $(document).on('click', '.add-model-btn', function() {
                _modelModalTitle.text('Add');
                _modelSpoofInput.val('POST');
                $('#make_id').val('');
                $('#model_id').val('');
                $('#model').val('');
                $('#model-modal').modal('show');
            });

            // edit model
            $(document).on('click', '.edit-model-btn', function() {
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
                            $('#make_id').val(data['make_id']).change();
                            $('#model').val(data['model']);
                            $('#model_id').val(data['id']);

                            // set the update url
                            var action =  _Form .attr('action');
                            // action = action + '/' + season_id;
                            console.log(action);
                            _Form .attr('action', action);

                            // open the modal
                            $('#model-modal').modal('show');
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
                        <h4 class="card-title">Vehicle Makes</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-make-btn">
                                Add Vehicle Make
                            </button>
                        </div>

                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="makes-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Make</th>
                                        <th>Models</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Make</th>
                                        <th>Models</th>
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
                        <h4 class="card-title">Vehicle Models</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-model-btn">
                                Add Vehicle Model
                            </button>
                        </div>

                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="models-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Model</th>
                                        <th>Make</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Model</th>
                                        <th>Make</th>
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
    <div class="modal fade" id="make-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"><span id="make-modal-title">Add</span> Vehicle Make</h4>
                </div>
                <div class="modal-body" >
                    <form id="makeform" action="{{ url('auto/makes') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="make-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="make">Vehicle Make</label>
                                    <input type="text" value="{{ old('make') }}" class="form-control" id="make" name="make" required />
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

    <div class="modal fade" id="model-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"><span id="model-modal-title">Add</span> Vehicle Model</h4>
                </div>
                <div class="modal-body" >
                    <form id="modelform" action="{{ url('auto/models') }}" method="post" id="model-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="model-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Make" tabindex="-98"
                                            name="make_id" id="make_id" required>
                                        @foreach($makes as $make)
                                            <option value="{{$make->id}}">{{$make->make}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="max_period_months">Model Name</label>
                                    <input type="text" value="{{ old('model') }}" class="form-control" id="model" name="model" required />
                                </div>
                            </div>
                        </div>






                        <input type="hidden" name="model_id" id="model_id"/>
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
