@extends('layouts.app')
@section('title', 'Loan Products')
@push('js')
    <script>

        $(function() {
            // server side - lazy loading
            $('#products-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('valuation-matrix-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'car_category', name: 'car_category'},
                    {data: 'yom', name: 'yom'},
                    {data: 'collateral_fsv', name: 'collateral_fsv'},
                    {data: 'min_loan', name: 'min_loan'},
                    {data: 'max_loan', name: 'max_loan'},
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
                    searchPlaceholder: "Search Matrix",
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
                $('#max_loan').val('');
                $('#min_loan').val('');
                $('#collateral_fsv').val('');
                $('#yom').val('');
                $('#id').val('');
                $('#user-modal').modal('show');
            });


            // edit   product
            $(document).on('click', '.edit-matrix-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id'),
                    _Form = $('#user-form');

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
                            $('#max_loan').val(data['max_loan']);
                            $('#min_loan').val(data['min_loan']);
                            $('#collateral_fsv').val(data['collateral_fsv']);
                            $('#yom').val(data['yom']);
                            $("#category_id").val(data['car_category_id']).change();
                            $('#id').val(data['id']);

                            // set the update url
                            var action =  _Form .attr('action');
                            // action = action + '/' + season_id;
                            console.log(action);
                            _Form .attr('action', action);

                            // open the modal
                            $('#user-modal').modal('show');
                        }
                    });
                }
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
                        <h4 class="card-title">Valuation Matrix</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-btn">
                                Add Valuation Matrix
                            </button>
                        </div>
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="products-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category</th>
                                        <th>YOM</th>
                                        <th>% Coll. FSV</th>
                                        <th>Min Loan</th>
                                        <th>Max Loan</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category</th>
                                        <th>YOM</th>
                                        <th>% Coll. FSV</th>
                                        <th>Min Loan</th>
                                        <th>Max Loan</th>
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
                    <h4 class="modal-title" id="myModalLabel"> Add to Valuation Matrix</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('products/valuation') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group ">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Category" tabindex="-98"
                                            name="category_id" id="category_id" required>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}">{{$category->category}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

{{--                            <div class="col-md-6">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label class="control-label" for="max_period_months">Maximum period(Months)</label>--}}
{{--                                    <input type="number" value="{{ old('max_period_months') }}" class="form-control" id="max_period_months" name="max_period_months" required />--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="yom">YOM (Year of Manufacture)</label>
                                    <input type="number" value="{{ old('yom') }}" class="form-control" id="yom" name="yom" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="collateral_fsv">% Collateral FSV</label>
                                    <input type="number" step=".01" value="{{ old('collateral_fsv') }}" class="form-control" id="collateral_fsv" name="collateral_fsv" required />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="min_loan">Min Loan (KES)</label>
                                    <input type="number" value="{{ old('min_loan') }}" class="form-control" id="min_loan" name="min_loan" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="max_loan">Max Loan (KES)</label>
                                    <input type="number" value="{{ old('max_loan') }}" class="form-control" id="max_loan" name="max_loan" required />
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
