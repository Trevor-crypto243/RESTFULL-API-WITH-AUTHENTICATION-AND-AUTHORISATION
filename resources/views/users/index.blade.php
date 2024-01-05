@extends('layouts.app')
@section('title', 'All Users')
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

            var _ModalTitle = $('#user-modal-title'),
                _SpoofInput = $('#user-spoof-input'),
                _Form = $('#user-form');
            
            //delete user action
            $(document).on('click','.delete-user-btn',function(){
                var id = $(this).attr('del-id');
                if(id == ''){
                    // alert("Id is null");
                    $('id_deta').val("0791560919");

                }else if(id !== ''){
                    // alert("The id is ",id)
                    console.log("The id is ", id)
                    $('#id').val(id);
                    $('#id_detail').val(id);


                }
                
                // open delete modal
                $('#delete-modal').modal('show');
            });

            // edit   product
            $(document).on('click', '.edit-user-btn', function() {
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
                            $('#name').val(data['name']);
                            $('#surname').val(data['surname']);
                            $('#email').val(data['email']);
                            $('#phone_no').val(data['phone_no']);
                            $('#id_no').val(data['id_no']);
                            $("#user_group").val(data['user_group']).change();
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
                        <h4 class="card-title">All Users</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#user-modal">
                                <i class="fa fa-plus"></i> Add New User
                            </button>

                            <form id="filter-form" class="form-inline form-horizontal" action="{{ '/users/search' }}" method="POST">
                                @csrf

                                <div class="form-group ">


                                    <div class="form-group text-left mb-2 mx-sm-3">
                                        <label class="control-label" for="id_no">ID Number</label>
                                        <input type="text" name="id_no" id="id-no" value="{{$id_no}}" class="form-control"/>
                                    </div>


                                    <div class="form-group text-left mb-2 mx-sm-3">
                                        <label class="control-label" for="phone_no">Phone Number (254...)</label>
                                        <input type="number" name="phone_no" id="phone-no" value="{{$phone_no}}" class="form-control"/>
                                    </div>



                                </div>

                                <div class="form-group mb-2">
                                    <button type="submit" class="btn btn-success btn-sm"> Search</button>
                                </div>
                            </form>

                        </div>
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="users-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>Group</th>
                                        <th>Email</th>
                                        <th>ID.NO</th>
                                        <th>Phone No.</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                           {{$user->id}}
                                        </td>
                                        <td>
                                            {{$user->name}}
                                        </td>
                                        <td>
                                            {{$user->surname}}
                                        </td>
                                        <td>
                                            {{ optional($user->role)->name}}
                                        </td>
                                        <td>
                                            {{$user->email}}
                                        </td>
                                        <td>
                                            {{$user->id_no}}
                                        </td>
                                        <td>
                                            {{$user->phone_no}}
                                        </td>
                                        <td>
                                            <button source="{{route('edit-user' ,  $user->id)}}"
                                                    class="btn btn-primary btn-link btn-sm edit-user-btn" acs-id="{{$user->id}}">
                                                <i class="material-icons">edit</i> Edit
                                            </button>

                                            <button source="{{route('delete-user' ,  $user->id)}}"
                                                    class="btn btn-danger btn-link btn-sm delete-user-btn" del-id="{{$user->id}}">
                                                <i class="material-icons">delete</i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>Group</th>
                                        <th>Email</th>
                                        <th>ID.NO</th>
                                        <th>Phone No.</th>
                                        <th>Actions</th>
                                    </tr>
                                </tfoot>
                            </table>
                            {{$users->links()}}
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

    
    



    {{-- modal--}}
    <div class="modal fade" id="user-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Add </span> New User</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('enroll') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="name">Name</label>
                                    <input type="text" value="{{ $edit ? $selected_user->name : old('name') }}" class="form-control" id="name" name="name" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="surname">Surname</label>
                                    <input type="text" value="{{ $edit ? $selected_user->surname : old('surname') }}" class="form-control" id="surname" name="surname" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="email">Email</label>
                                    <input type="email" value="{{$edit ? $selected_user->email :  old('email') }}" class="form-control pb-0 mt-2" name="email" id="email" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="id_no">ID. NO</label>
                                    <input type="text" value="{{$edit ? $selected_user->id_no :  old('id_no') }}" class="form-control pb-0 mt-2" name="id_no" id="id_no" required/>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="phone_no">Phone No.</label>
                                    <input id="phone_no" type="tel"  class="form-control pb-0 mt-2"  name="" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group ">
                                    {{--<label class="control-label" for="user_role" style="line-height: 6px;">User Role</label>--}}

                                    <div class="dropdown bootstrap-select show-tick">
                                        <select class="selectpicker" data-style="select-with-transition" title="Choose User Group" tabindex="-98"
                                                name="user_group" id="user_group" required>
                                            @foreach( $user_roles as $user_role)
                                                <option value="{{ $user_role->id  }}">{{ $user_role->name }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>
                            </div>


                        </div>



                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Save</button>
                        </div>

                    </form>
                    {{--hidden fields--}}

                </div>
    
            </div>
        </div>
    </div>

    {{--delete user modal--}}
    <div class="modal fade" id="delete-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title text-danger" id="myModalLabel"> <span id="user-modal-title">Delete User</h4>

                </div>
                <div class="modal-body" >
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Are you sure you want to delete the user with the following id?  </h4>
                    <h5 id="id"></h5>
                    <form action="{{url('delete')}}" method="post">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <div class="form-group">       
                             
                                                           
                            <input id="id_detail" type="tel" class="form-control pb-0 mt-2 outline-none"  value="" name="id_detail"/>                                              

                            <button type="button" class="btn btn-sm" data-dismiss="modal"><i class="material-icons">close</i> Cancel</button>
                            <button class="btn btn-danger btn-sm" id="save-brand"><i class="material-icons">delete</i> Delete</button>
                        </div>
                    </form>
                </div>    
            </div>
        </div>
    </div>
@endsection
