@extends('layouts.app')
@section('title', 'Customer Profile')

@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#loans-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('customer-loans-dt', $customer->user_id) }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'product', name: 'product'},
                    {data: 'amount_requested', name: 'amount_requested'},
                    {data: 'period_in_months', name: 'period_in_months'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'approved_date', name: 'approved_date'},
                    {data: 'approval_status', name: 'approval_status'},
                    {data: 'repayment_status', name: 'repayment_status'},
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
                    searchPlaceholder: "Search Loans",
                },
                "order": [[0, "desc"]]
            });

            // live search
        });

        // $('.suspend-model-form').on('submit', function() {
        //     if (confirm('Are you sure you want to suspend this user?')) {
        //         return true;
        //     }
        //     return false;
        // });

        $('.unsuspend-model-form').on('submit', function() {
            if (confirm('Are you sure you want to unsuspend this user?')) {
                return true;
            }
            return false;
        });

        // $('.block-model-form').on('submit', function() {
        //     if (confirm('Are you sure you want to block this user?')) {
        //         return true;
        //     }
        //     return false;
        // });

        $('.unblock-model-form').on('submit', function() {
            if (confirm('Are you sure you want to unblock this user?')) {
                return true;
            }
            return false;
        });
    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8" >
                <div class="card" style="margin-top: 0px">
                    <div class="card-header card-header-icon card-header-primary">
                        <div class="card-icon">
                            <i class="material-icons">perm_identity</i>
                        </div>
                        <h4 class="card-title">Customer Profile
{{--                            <small class="category">Update your profile</small>--}}
                        </h4>
                    </div>
                    <div class="card-body">
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <div class="row">

                            <div class="col-md-4">
                                <ul class="list-group">
                                    <li class="list-group-item"><span class="text-muted">Surname:</span> {{$customer->user->surname}}</li>
                                    <li class="list-group-item"><span class="text-muted">Name:</span> {{$customer->user->name}}</li>
                                    <li class="list-group-item"><span class="text-muted">Phone No:</span> {{$customer->user->phone_no}}</li>
                                    <li class="list-group-item"><span class="text-muted">ID No.:</span> {{$customer->user->id_no}}</li>
                                    <li class="list-group-item"><span class="text-muted">E-Mail:</span> {{$customer->user->email}}</li>

                                    <br>

                                    <li class="list-group-item">
                                        <span class="text-muted">Wallet Balance</span>
                                        <br>
                                        {{optional($customer->user->wallet)->currency.' '.number_format(optional($customer->user->wallet)->current_balance)}}
                                        <a style="padding-left: 0px" href="{{url('wallet/customer/'.$customer->user->wallet_id)}}"
                                           class="btn btn-primary btn-link btn-sm">
                                            <i class="material-icons">account_balance_wallet</i> View Wallet
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-md-4">
                                <ul class="list-group">
                                    <li class="list-group-item"><span class="text-muted">Is Checkoff?:</span> {{$customer->is_checkoff ? 'YES' : 'NO'}}</li>
                                    <li class="list-group-item"><span class="text-muted">Overdraft Limit:</span> {{optional(optional($customer->user)->wallet)->currency.' '.number_format($customer->max_limit)}}</li>
                                    <li class="list-group-item"><span class="text-muted">Status:</span>
                                        @if($customer->status == 'active')
                                            <span class="badge pill badge-success">{{$customer->status}}</span>
                                        @elseif($customer->status == 'suspended')
                                            <span class="badge pill badge-warning">{{$customer->status}}</span>
                                        @elseif($customer->status == 'blocked')
                                            <span class="badge pill badge-danger">{{$customer->status}}</span>
                                        @endif
                                    </li>
                                    <li class="list-group-item"><span class="text-muted">Total Loans:</span> {{optional($customer->user)->loans->count()}}</li>

                                    <li class="list-group-item">
                                        <span class="text-muted">Advance Applications ({{\App\AdvanceApplication::where('user_id',$customer->user_id)->count()}})</span>
                                        <br>
                                        <a style="padding-left: 0px" target="_blank" href="{{url('advance/requests/user/'.$customer->user_id)}}"
                                           class="btn btn-primary btn-link btn-sm">
                                            <i class="material-icons">account_balance_wallet</i> View All
                                        </a>
                                    </li>
                                </ul>
                            </div>


                            <div class="col-md-4">
                                @if(!is_null(\App\Employee::where('user_id', $customer->user_id)->first()))
                                    <a href="{{\App\Employee::where('user_id', $customer->user_id)->first()->passport_photo_url}}" target="_blank">
                                        <img src="{{\App\Employee::where('user_id', $customer->user_id)->first()->passport_photo_url}}" width="100%" height="200dp"
                                             style="margin-bottom: 1rem;" alt="Photo">
                                    </a>
                                @endif
                            </div>

                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header card-header-icon card-header-primary">
                        <div class="card-icon">
                            <i class="material-icons">money</i>
                        </div>
                        <h4 class="card-title">Loans -
                            <small class="category">Loan request history</small>
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="material-datatables">
                            <table id="loans-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Applied On</th>
                                    <th>Approved On</th>
                                    <th>Approval</th>
                                    <th>Repayment</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Applied On</th>
                                    <th>Approved On</th>
                                    <th>Approval</th>
                                    <th>Repayment</th>
                                    <th>Actions</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

            </div>


            <div class="col-md-4">
                <div class="card card-blog" style="margin-top: 0px">

                    <div class="card-body">
                        <h6 class="card-category text-gray">{{ $customer->user->role->name }}</h6>
                        <h4 class="card-title">{{ $customer->user->name.' '.$customer->user->surname}}</h4>

                        <p>You can perform customer specific actions on this page</p>
                        <br>

                        @if(auth()->user()->role->has_perm([37]))
                            <p >Update Overdraft limit</p>

                            <form action="{{url('customer/overdraft/update')}}" method="post" class="form-horizontal" enctype="multipart/form-data">
                                {{ csrf_field() }}
                                {{--spoofing--}}

                                <input type="hidden" name="customer_id" value="{{$customer->id}}">

                                <div class="row" style="margin-left: 0px; margin-right: 0px">
                                    <div class=" col-md-6">
                                        <input id="new_limit" type="number"  class="form-control pb-0"  name="amount" placeholder="New Limit" required/>
                                    </div>

                                    <div class="col-md-6">
                                        <button class="btn btn-success btn-sm" id="save-brand"><i class="material-icons">save</i> Update Limit</button>

                                    </div>
                                </div>
                            </form>
                        @endif
                        <br>

                        @if(auth()->user()->role->has_perm([38]))

                            @if($customer->status == 'active')
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#suspend-modal">
                                    Suspend Profile
                                </button>
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#block-modal">
                                    Block Profile
                                </button>
                            @elseif($customer->status == 'suspended')
                                <form action="{{ url('customer/unsuspend') }}" method="post" style="display: inline;" class="unsuspend-model-form">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="id" value="{{$customer->id}}">
                                    <button class="btn btn-success btn-sm">Unsuspend Profile</button>
                                </form>

                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#block-modal">
                                    Block Profile
                                </button>
                            @elseif($customer->status == 'blocked')
                                <form action="{{ url('customer/unblock') }}" method="post" style="display: inline;" class="unblock-model-form">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="id" value="{{$customer->id}}">
                                    <button class="btn btn-success btn-sm">Unblock Profile</button>
                                </form>
                            @endif
                        @endif

                        <br>
                        <h6 class="card-category text-gray mt-3">Suspension History</h6>

                        <div class="card-content table-responsive table-full-width">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Action</th>
                                    <th>Reason</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach(\App\CustomerSuspension::where('customer_profile_id',$customer->id)->orderBy('id','desc')->get() as $suspension)
                                    <tr>
                                        <td>
                                            {{\Carbon\Carbon::parse($suspension->created_at)->isoFormat('MMMM Do YYYY')}}
                                        </td>
                                        <td>
                                            {{$suspension->action_type}}
                                        </td>
                                        <td>
                                            {{$suspension->reason}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                        </div>


                    </div>
                </div>
            </div>
        </div>

    </div>


    {{--modal--}}
    <div class="modal fade" id="suspend-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> Suspend Profile</h4>
                    {{--                    <h4 class="modal-title" id="myModalLabel"> {{ $edit ?'Edit' : 'Add' }} Role</h4>--}}
                </div>
                <div class="modal-body" >

                    <form action="{{ url('customer/suspend') }}"  method="post" id="group-form">
                        {{ csrf_field() }}

                        <div class="form-group mb-4">
                            <label class="control-label" for="reason">Reason</label>
                            <input type="text" value="{{ old('reason') }}" class="form-control" id="reason" name="reason" required/>
                        </div>


                        <input type="hidden" id="id" name="id" value="{{$customer->id}}"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Suspend</button>
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


    <div class="modal fade" id="block-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> Block Profile</h4>
                    {{--                    <h4 class="modal-title" id="myModalLabel"> {{ $edit ?'Edit' : 'Add' }} Role</h4>--}}
                </div>
                <div class="modal-body" >

                    <form action="{{ url('customer/block') }}"  method="post" id="group-form">
                        {{ csrf_field() }}

                        <div class="form-group mb-4">
                            <label class="control-label" for="reason">Reason</label>
                            <input type="text" value="{{ old('reason') }}" class="form-control" id="reason" name="reason" required/>
                        </div>


                        <input type="hidden" id="id" name="id" value="{{$customer->id}}"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Suspend</button>
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
