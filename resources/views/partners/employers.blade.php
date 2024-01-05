@extends('layouts.app')
@section('title', 'Employers')
@push('js')
    <script>

        const phoneInputField = document.querySelector("#phone_no");
        const phoneInput = window.intlTelInput(phoneInputField, {
            initialCountry: "ke",
            hiddenInput: "phone_no",
            utilsScript:
                "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });

        $(function() {
            // server side - lazy loading
            $('#employers-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('employers-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'business_logo', name: 'business_logo'},
                    {data: 'business_name', name: 'business_name'},
                    {data: 'business_address', name: 'business_address'},
                    {data: 'business_email', name: 'business_email'},
                    {data: 'business_phone_no', name: 'business_phone_no'},
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
                    searchPlaceholder: "Search Employers",
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
                $('#name').val('');
                $('#description').val('');
                $('#address').val('');
                $('#reg_no').val('');
                $('#email').val('');
                $('#phone_no').val('');
                $('#id').val('');
                $('#user-modal').modal('show');
            });


            // edit   product
            $(document).on('click', '.edit-employers-btn', function() {
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
                            $('#name').val(data['business_name']);
                            $('#description').val(data['business_desc']);
                            $('#address').val(data['business_address']);
                            $('#reg_no').val(data['business_reg_no']);
                            $('#email').val(data['business_email']);
                            $('#phone_no').val(data['business_phone_no']);
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
                            <i class="material-icons">work</i>
                        </div>
                        <h4 class="card-title">Employers</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">

                            <button class="btn btn-primary btn-sm add-btn">
                                Add EMployer
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
                            <table id="employers-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>E-Mail</th>
                                        <th>Phone No.</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>E-Mail</th>
                                        <th>Phone No.</th>
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
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Add </span> Employer</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('partners') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                    <div class="fileinput-new thumbnail img-circle">
                                        <img src="{{url('assets/img/placeholder.jpg')}}" alt="...">
                                    </div>
                                    <div class="fileinput-preview fileinput-exists thumbnail img-circle"></div>
                                    <div>
                                      <span class="btn btn-round btn-rose btn-file">
                                        <span class="fileinput-new">Add Logo</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="file" name="business_logo" />
                                      </span>
                                        <br />
                                        <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                    </div>
                                </div>

                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="name">Business Name</label>
                                    <input type="text" value="{{ old('name') }}" class="form-control" id="name" name="name" required />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="description">Business Description</label>
                                    <textarea class="form-control" name="description" id="description" rows="3">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="address">Address</label>
                                    <input type="text" value="{{ old('address') }}" class="form-control" id="address" name="address" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="reg_no">Registration No.</label>
                                    <input type="text" value="{{ old('reg_no') }}" class="form-control" id="reg_no" name="reg_no" required />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="email">E-Mail</label>
                                    <input type="text" value="{{ old('email') }}" class="form-control" id="email" name="email" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="phone_no">Phone No.</label>
                                    <input id="phone_no" type="tel"  class="form-control pb-0 mt-2"  name="" required/>
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
