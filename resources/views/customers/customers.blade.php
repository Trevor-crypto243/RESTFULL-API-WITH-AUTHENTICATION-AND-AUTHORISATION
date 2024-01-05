@extends('layouts.app')
@section('title', 'All Customers')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">list</i>
                        </div>
                        <h4 class="card-title">All Customers</h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">
                            <a href="{{url('customers/all/export')}}" class="btn btn-success btn-sm">
                                <span class="material-icons">file_download </span> Export Excel
                            </a>

                            <form id="filter-form" class="form-inline form-horizontal" action="{{ '/customers/search' }}" method="POST">
                                @csrf

                                <div class="form-group ">


                                    <div class="form-group text-left mb-2 mx-sm-3">
                                        <label class="control-label" for="id_no">ID Number</label>
                                        <input type="text" name="id_no" id="id-no" class="form-control"/>
                                    </div>


                                    <div class="form-group text-left mb-2 mx-sm-3">
                                        <label class="control-label" for="phone_no">Phone Number (254...)</label>
                                        <input type="number" name="phone_no" id="phone-no" class="form-control"/>
                                    </div>



                                </div>

                                <div class="form-group mb-2">
                                    <button type="submit" class="btn btn-success btn-sm"> Search</button>
                                </div>
                            </form>

                        </div>

                        @include('layouts.common.success')
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
                                        <th>Is Checkoff</th>
                                        <th>Status</th>
                                        <th>Loans</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($customers as $customer)
                                    <tr>
                                        <td>
                                            {{$customer->id}}
                                        </td>
                                        <td>
                                            {{optional($customer->user)->name}}
                                        </td>
                                        <td>
                                            {{optional($customer->user)->surname}}
                                        </td>
                                        <td>
                                            {{optional($customer->user)->id_no}}
                                        </td>

                                        <td>
                                            {{optional($customer->user)->phone_no}}
                                        </td>

                                        <td>
                                            {{$customer->is_checkoff ? 'YES' : 'NO'}}
                                        </td>

                                        <td>
                                            @if ($customer->status == 'active')
                                                <span class="badge pill badge-success">{{$customer->status}}</span>
                                            @elseif ($customer->status == 'suspended')
                                                <span class="badge pill badge-warning">{{$customer->status}}</span>
                                            @elseif ($customer->status == 'blocked')
                                                <span class="badge pill badge-danger">{{$customer->status}}</span>
                                            @else
                                                <span class="badge pill badge-info">{{$customer->status}}</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{optional($customer->user)->loans->count()}}
                                        </td>
                                        <td>
                                                <div class="align-content-center">

                                                <a href="{{route('customer-details' ,  $customer->id)}}"
                                                                class="btn btn-primary btn-link btn-sm">
                                                    <i class="material-icons">perm_identity</i> Profile</a>

                                                @if ($customer->is_checkoff )
                                                    <a href="{{route('employee-details' , is_null(\App\Employee::where('user_id',$customer->user_id)->first()) ? 0 :
                                                        \App\Employee::where('user_id',$customer->user_id)->first()->id)}}"
                                                                class="btn btn-primary btn-link btn-sm">
                                                    <i class="material-icons">badge</i> Employee</a>
                                                @endif

                                                @if (auth()->user()->role->has_perm([22]) )
                                                    <a href="{{url('wallet/customer/'.optional($customer->user)->wallet_id)}}"
                                                                class="btn btn-primary btn-link btn-sm">
                                                    <i class="material-icons">account_balance_wallet</i> Wallet</a>
                                                @endif

                                               </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Surname</th>
                                        <th>ID No.</th>
                                        <th>Phone No.</th>
                                        <th>Is Checkoff</th>
                                        <th>Status</th>
                                        <th>Loans</th>
                                        <th>Actions</th>
                                    </tr>
                                </tfoot>
                            </table>

                            {{$customers->links()}}
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

@endsection
