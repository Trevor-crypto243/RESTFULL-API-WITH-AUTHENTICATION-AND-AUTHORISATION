@extends('layouts.app')
@section('title', 'Bulk SMS')
@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#sms-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('bulk-sms-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'recipients', name: 'recipients'},
                    {data: 'message', name: 'message'},
                    {data: 'created_by', name: 'created_by'},
                    {data: 'created_at', name: 'created_at'},
                    // {data: 'actions', name: 'actions'},
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
                    searchPlaceholder: "Search SMS",
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
            $(document).on('click', '.specify-btn', function() {
                _ModalTitle.text('Add');
                _SpoofInput.val('POST');
                // $('#name').val('');
                // $('#detail').val('');
                // $('#id').val('');
                $('#specify-modal').modal('show');
            });

            $(document).on('click', '.upload-btn', function() {
                _ModalTitle.text('Add');
                _SpoofInput.val('POST');
                // $('#name').val('');
                // $('#detail').val('');
                // $('#id').val('');
                $('#upload-modal').modal('show');
            });

            $(document).on('click', '.custom-btn', function() {
                _ModalTitle.text('Add');
                _SpoofInput.val('POST');
                // $('#name').val('');
                // $('#detail').val('');
                // $('#id').val('');
                $('#custom-modal').modal('show');
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
                        <h4 class="card-title">Bulk SMS</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-btn">
                                Send to user group
                            </button>

                            <button class="btn btn-primary btn-sm specify-btn">
                                Specify Recipients
                            </button>

                            <button class="btn btn-primary btn-sm upload-btn">
                               Upload CSV
                            </button>

                            <button class="btn btn-primary btn-sm custom-btn">
                               Send to Custom List
                            </button>
                        </div>
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="sms-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Recipients</th>
                                        <th>Message</th>
                                        <th>Created By</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Recipients</th>
                                        <th>Message</th>
                                        <th>Created By</th>
                                        <th>Date Created</th>
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
                    <form id="userform" action="{{ url('bulk/messaging/group') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group ">

                                    <select class="selectpicker" data-style="select-with-transition" title="Choose User Group" tabindex="-98"
                                            name="user_group" id="user_group" required>
                                        <option value="">Select group</option>

                                        @foreach( $userGroups as $userGroup)
                                            <option value="{{ $userGroup->id  }}">{{ $userGroup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="message">Message</label>
                                    <textarea name="message" rows="5" id="message" class="form-control" required></textarea>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close"></i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="fa fa-save"></i> Send</button>
                        </div>

                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="specify-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"> Send Bulk SMS to specific recipients</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('bulk/messaging/specify') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="recipients">Recipient numbers (comma separated)</label>
                                    <input type="text" value="{{ old('recipients') }}" class="form-control" id="recipients" name="recipients" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="message">Message</label>
                                    <textarea name="message" rows="5" id="message" class="form-control" required></textarea>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close"></i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="fa fa-save"></i> Send</button>
                        </div>

                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="product-modal-title">Upload </span> Recipients</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('bulk/messaging/upload') }}" method="post" id="product-form"  enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="product-spoof-input" value="PUT" disabled/>



                        <div class="col-md-4 text-center">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                {{--<div class="fileinput-new thumbnail img-responsive">--}}
                                {{--<img src="" alt="Product Image">--}}
                                {{--</div>--}}
                                <div class="fileinput-preview fileinput-exists thumbnail img-responsive"></div>
                                <div>
                                    <span class="btn btn-round btn-rose btn-file">
                                        <span class="fileinput-new">Select File</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="file"  name="file" />
                                    </span>
                                    <br />
                                    <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                </div>

                                <a href="{{url('samples/bulk_messaging.csv')}}">Download sample</a>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="message">Message</label>
                                    <textarea name="message" rows="5" id="message" class="form-control" required></textarea>
                                </div>
                            </div>
                        </div>



                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Upload and Send</button>
                        </div>
                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="custom-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"> Send Bulk SMS to custom group</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('bulk/messaging/custom') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group ">

                                    <select class="selectpicker" data-style="select-with-transition" title="Choose User Group" tabindex="-98"
                                            name="user_group" id="user_group" required>
                                        <option value="">Select group</option>
                                         <option value="1">Checkoff Customers</option>
                                         <option value="2">Customers with Active Loans</option>
                                         <option value="3">Customers with amendment loans</option>
                                         <option value="4">Customers with arrears</option>
                                    </select>
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="message">Message</label>
                                    <textarea name="message" rows="5" id="message" class="form-control" required></textarea>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close"></i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="fa fa-save"></i> Send</button>
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
