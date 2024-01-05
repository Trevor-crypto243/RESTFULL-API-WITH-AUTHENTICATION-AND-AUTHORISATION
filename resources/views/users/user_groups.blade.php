@extends('layouts.app')
@section('title', 'User roles')
@push('js')
    <script>
        $(function() {
            $('#roles-dt').DataTable({
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ]
            });
        });


        // edit   product
        $(document).on('click', '.edit', function() {
            var seasonBtn = $(this);
            var season_id = seasonBtn.attr('acs-id'),
                seasonForm = $('#group-form');

            if (season_id !== '') {
                $.ajax({
                    url: 'user_groups/'+season_id,
                    type: 'get',
                    dataType: 'json',
                    // beforeSend: function() {
                    //     seasonModalTitle.text('Edit');
                    //     seasonSpoofInput.removeAttr('disabled');
                    // },
                    success: function(data) {
                        console.log(data);
                        // populate the modal fields using data from the server
                        $('#group_name').val(data['name']);
                        $('#id').val(data['id']);

                        // set the update url
                        var action =  'user_groups/update';
                        // action = action + '/' + season_id;
                        console.log(action);
                        seasonForm .attr('action', action);

                        // open the modal
                        $('#user-role-modal').modal('show');
                    }
                });
            }
        });




        // delete javascript
        $('.delete-model-form').on('submit', function() {
            if (confirm('Are you sure you want to delete the user role ?')) {
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
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">assignment</i>
                        </div>
                        <h4 class="card-title">Manage User Groups</h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#user-role-modal">
                                <i class="fa fa-plus"></i> Add User Group
                            </button>
                        </div>
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        <div class="material-datatables">
                            <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Group Name</th>
                                    <th>Users</th>
                                    <th class="disabled-sorting text-right">Actions</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Group Name</th>
                                    <th>Users</th>
                                    <th class="disabled-sorting text-right">Actions</th>
                                </tr>
                                </tfoot>
                                <tbody>
                                @if(count($user_groups))
                                    @foreach($user_groups as $user_group)
                                        <tr>
                                            <td>{{$user_group->id}}</td>
                                            <td>{{$user_group->name}}</td>
                                            <td>{{$user_group->users->count()}}</td>
                                            <td class="text-right">
                                                <button acs-id="{{$user_group->id}}" class="btn btn-sm btn-link btn-warning btn-just-icon edit"><i class="material-icons">edit</i></button>
                                                <form action="{{ url('user_groups/delete') }}" method="post" style="display: inline;" class="delete-model-form">
                                                    {{ csrf_field() }}
                                                    <input type="hidden" name="id" value="{{$user_group->id}}">
                                                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                                </form>
                                                <a href="{{ url('users/groups/'.$user_group->id) }}" class="btn btn-sm btn-link bg-success">
                                                    <i class="fa fa-check" style="color: white"></i><span style="color: white"> Manage Access</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
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
    <div class="modal fade" id="user-role-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> Add User Group</h4>
{{--                    <h4 class="modal-title" id="myModalLabel"> {{ $edit ?'Edit' : 'Add' }} Role</h4>--}}
                </div>
                <div class="modal-body" >

                    <form action="{{ url('user_groups') }}"  method="post" id="group-form">
                        {{ csrf_field() }}

                        <div class="form-group mb-4">
                            <label class="control-label" for="group_name">Group Name</label>
                            <input type="text" value="{{ old('group_name') }}" class="form-control" id="group_name" name="group_name" required/>
                        </div>


                        <input type="hidden" id="id" name="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Save</button>
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
