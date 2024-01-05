@extends('layouts.app')
@section('title', 'Application Details')
@push('js')
    <script>


        $('.approve-loan-form').on('submit', function() {
            if (confirm('Are you sure you want to approve this request?')) {
                return true;
            }
            return false;
        });

        $('.send-to-hr-form').on('submit', function() {
            if (confirm('Are you sure you want to send this request to HR for approval?')) {
                return true;
            }
            return false;
        });

    </script>
@endpush

@section('content')
    <div class="container-fluid" style="margin-top: -50px">


        <div class="row">
            <div class="col-md-12">
                <div class="page-categories">
                    <h3 class=" text-center">Application Details</h3>
                    <br />
                    <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#link7" role="tablist">
                                <i class="material-icons">info</i> advance Request
                            </a>
                        </li>
                    </ul>


                    <div class="tab-content tab-space tab-subcategories">

                        @include('layouts.common.success')
                        @include('layouts.common.warning')
                        @include('layouts.common.warnings')

                        {{--ABOUT--}}
                        <div class="tab-pane active" id="link7">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">info</i>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <h4 class="card-title">
                                                {{optional($advanceApplication->user)->name.' '.optional($advanceApplication->user)->surname }}
                                                <a href="{{url('customers/details',optional(\App\CustomerProfile::where('user_id', $advanceApplication->user_id)->first())->id)}}"
                                                   class="btn btn-info btn-sm" target="_blank">View User Profile
                                                </a>
                                            </h4>
                                        </div>
                                        <div class="col-md-8">
                                            @if($advanceApplication->quicksava_status == 'PROCESSING')
                                                <div class="row mt-1">

                                                    @if($advanceApplication->hr_status == 'ACCEPTED')
                                                        @if(auth()->user()->role->has_perm([10]))
                                                            <div class="col-md-4">
                                                                <form action="{{ url('advance/requests/approve') }}" method="post" style="display: inline;" class="approve-loan-form">
                                                                    {{ csrf_field() }}
                                                                    <input type="hidden" name="request_id" value="{{$advanceApplication->id}}">
                                                                    <button class="btn btn-success btn-sm">Approve Request</button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    @elseif($advanceApplication->hr_status == 'AMENDMENT')
                                                        @if(auth()->user()->role->has_perm([42]))
                                                            <div class="col-md-4">
                                                                <form action="{{ url('advance/requests/send_to_hr') }}" method="post" style="display: inline;" class="send-to-hr-form">
                                                                    {{ csrf_field() }}
                                                                    <input type="hidden" name="request_id" value="{{$advanceApplication->id}}">
                                                                    <button class="btn btn-success btn-sm">Send to HR</button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(auth()->user()->role->has_perm([41]))
                                                        <div class="col-md-4">
                                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#amend-modal">
                                                                Request Amendment
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if(auth()->user()->role->has_perm([40]))
                                                        <div class="col-md-4">
                                                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-modal">
                                                                Reject Request
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif()

                                            @if($advanceApplication->quicksava_status == 'PENDING')
                                                <div class="row mt-1">

                                                    @if(auth()->user()->role->has_perm([41]))
                                                        <div class="col-md-4">
                                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#amend-modal">
                                                                Request Amendment
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if(auth()->user()->role->has_perm([40]))
                                                        <div class="col-md-4">
                                                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-modal">
                                                                Reject Request
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if(auth()->user()->role->has_perm([42]))
                                                        <div class="col-md-4">
                                                            <form action="{{ url('advance/requests/send_to_hr') }}" method="post" style="display: inline;" class="send-to-hr-form">
                                                                {{ csrf_field() }}
                                                                <input type="hidden" name="request_id" value="{{$advanceApplication->id}}">
                                                                <button class="btn btn-success btn-sm">Send to HR</button>
                                                            </form>
                                                        </div>
                                                    @endif

                                                </div>

                                            @endif()

                                            @if($advanceApplication->quicksava_status == 'AMENDMENT')
                                                <div class="row mt-1">

                                                    @if(auth()->user()->role->has_perm([41]))
                                                        <div class="col-md-4">
                                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#amend-modal">
                                                                Request Amendment
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if(auth()->user()->role->has_perm([40]))
                                                        <div class="col-md-4">
                                                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-modal">
                                                                Reject Request
                                                            </button>
                                                        </div>
                                                    @endif

                                                    @if(auth()->user()->role->has_perm([45]) && $advanceApplication->created_by == auth()->user()->id)
                                                        <div class="col-md-4">
                                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#amend-request-modal">
                                                                Amend Request
                                                            </button>
                                                        </div>
                                                    @endif

                                                </div>
                                            @endif()
                                        </div>

                                    </div>

                                </div>
                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-md-4">


                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Requested Amount:</td>
                                                    <td style="text-align: left">KES {{number_format($advanceApplication->amount_requested)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Period:</td>
                                                    <td style="text-align: left">{{$advanceApplication->period_in_months}} months</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Monthly deduction:</td>
                                                    <td style="text-align: left">KES {{number_format($monthlyAmount)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Applied on.:</td>
                                                    <td style="text-align: left">{{\Carbon\Carbon::parse($advanceApplication->created_at)->isoFormat('MMMM Do YYYY, hh:mm:ss')}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Approval status:</td>
                                                    <td style="text-align: left">
                                                        @if ($advanceApplication->quicksava_status == 'PENDING')
                                                            <span class="badge pill badge-info">{{$advanceApplication->quicksava_status}}</span>
                                                        @elseif ($advanceApplication->quicksava_status == 'ACCEPTED')
                                                            <span class="badge pill badge-success">{{$advanceApplication->quicksava_status}}</span>
                                                        @elseif ($advanceApplication->quicksava_status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{$advanceApplication->quicksava_status}}</span>
                                                        @elseif ($advanceApplication->quicksava_status == 'AMENDMENT')
                                                            <span class="badge pill badge-warning">{{$advanceApplication->quicksava_status}}</span>
                                                        @else
                                                            <span class="badge pill badge-primary">{{$advanceApplication->quicksava_status}}</span>
                                                        @endif

                                                    </td>
                                                </tr>

                                                @if($advanceApplication->created_by != null)
                                                    <tr>
                                                        <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Applied By.:</td>
                                                        <td style="text-align: left">{{optional(\App\User::find($advanceApplication->created_by))->name.' '.optional(\App\User::find($advanceApplication->created_by))->surname}}</td>
                                                    </tr>
                                                @endif

                                                </tbody>

                                            </table>
                                        </div>

                                        <div class="col-md-4">


                                            {{--                                            <h6 class="card-category text-gray">LOAN</h6>--}}
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Employed:</td>
                                                    <td style="text-align: left">
                                                        {{\Carbon\Carbon::parse($employee->employment_date)->diffForHumans()}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Basic Salary:</td>
                                                    <td style="text-align: left">
                                                        KES {{number_format($employee->basic_salary)}},
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Net Salary:</td>
                                                    <td style="text-align: left">
                                                        KES {{number_format($employee->net_salary)}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Monthly Ability:</td>
                                                    <td style="text-align: left">KES {{number_format($employee->max_limit)}}</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Monthly Limit:</td>
                                                    <td style="text-align: left">  KES {{number_format(($employee->max_limit*100)/113.33)}}</td>
                                                </tr>

                                                </tbody>

                                            </table>
                                        </div>

                                        <div class="col-md-4">
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Employer:</td>
                                                    <td style="text-align: left"> {{optional($employee->employer)->business_name}} <br>
                                                        ({{\Carbon\Carbon::parse($employee->employment_date)->diffForHumans()}})</td>
                                                </tr>


                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Job:</td>
                                                    <td style="text-align: left"> {{$employee->nature_of_work}} - {{$employee->position}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">National ID:</td>
                                                    <td style="text-align: left">
                                                        <a href="{{$employee->id_url}}" class="btn btn-success btn-sm" target="_blank">
                                                            View Front
                                                        </a>
                                                        <a href="{{$employee->id_back_url}}" class="btn btn-success btn-sm" target="_blank">
                                                            View Back
                                                        </a>
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Payslip:</td>
                                                    <td style="text-align: left">
                                                        <a href="{{$advanceApplication->payslip_url == null ? $employee->latest_payslip_url : $advanceApplication->payslip_url}}" class="btn btn-success btn-sm" target="_blank">
                                                            View Payslip
                                                        </a>
                                                    </td>
                                                </tr>



                                                </tbody>

                                            </table>


                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong class="category">HR Feedback</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <tbody>
                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Status</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        @if ($advanceApplication->hr_status == 'PENDING')
                                                            <span class="badge pill badge-info">{{$advanceApplication->hr_status}}</span>
                                                        @elseif ($advanceApplication->hr_status == 'ACCEPTED')
                                                            <span class="badge pill badge-success">{{$advanceApplication->hr_status}}</span>
                                                        @elseif ($advanceApplication->hr_status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{$advanceApplication->hr_status}}</span>
                                                        @elseif ($advanceApplication->hr_status == 'AMENDMENT')
                                                            <span class="badge pill badge-warning">{{$advanceApplication->hr_status}}</span>
                                                        @else
                                                            <span class="badge pill badge-info">{{$advanceApplication->hr_status}}</span>
                                                        @endif

                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>

                                            <br>
                                            <strong class="category">Loan Purpose:</strong><br>
                                            <p class="category">{{$advanceApplication->purpose}}</p>
                                        </div>

                                        <div class="col-md-4">
                                            <strong class="category">HR Comments:</strong>
                                            @foreach($employerComments as $employerComment)
                                                <p class="category">{{$employerComment->comment}} <br>
                                                    <i>{{$employerComment->user->name}} - {{ \Carbon\Carbon::parse($employerComment->created_at)->isoFormat('MMMM Do YYYY, hh:mm:ss')}}</i>
                                                </p>
                                            @endforeach

                                        </div>

                                        <div class="col-md-4">
                                            <strong class="category">Quicksava Comments:</strong>
                                            @foreach($systemComments as $systemComment)
                                                <p class="category">{{$systemComment->comment}} <br>
                                                    <i>{{$systemComment->user->name}} - {{ \Carbon\Carbon::parse($systemComment->created_at)->isoFormat('MMMM Do YYYY, hh:mm:ss')}}</i>
                                                </p>
                                            @endforeach

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>





    {{--modal--}}
    <div class="modal fade" id="reject-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Reject </span> advance Request</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('advance/requests/reject') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="name">Reason for rejecting</label>
                                    <textarea  class="form-control" name="reject_reason" required rows="5"></textarea>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="request_id" value="{{$advanceApplication->id}}">


                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Reject</button>
                        </div>

                    </form>
                    {{--hidden fields--}}

                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="amend-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Request </span> Amendment</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('advance/requests/amendment') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="amendment_details">Enter amendment request details</label>
                                    <textarea  class="form-control" name="amendment_details" required rows="5"></textarea>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="request_id" value="{{$advanceApplication->id}}">


                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Submit</button>
                        </div>

                    </form>
                    {{--hidden fields--}}

                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="amend-request-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Amend </span> advance application for customer </h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('customers/managed/advance/amend') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Choose Product" tabindex="-98"
                                            name="loan_product_id" id="loan_product_id" required>
                                        @foreach( \App\EmployerLoanProduct::where('employer_id',$employee->employer_id)->get() as $empLoanProduct)
                                            <option value="{{ $empLoanProduct->loan_product_id  }}" {{$empLoanProduct->loan_product_id == $advanceApplication->loan_product_id ? 'selected' : ''}}> {{ $empLoanProduct->loan_product->name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="payslip_file">Latest Payslip</label>
                                <input class="form-control" type="file" id="payslip_file" name="payslip_file">
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="amount_requested">Amount Requested</label>
                                    <input type="number" class="form-control pb-0 mt-2" name="amount_requested" value="{{$advanceApplication->amount_requested}}" id="amount_requested" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="period_in_months">Payment Period (Months)</label>
                                    <input type="number" class="form-control pb-0 mt-2" name="period_in_months" value="{{$advanceApplication->period_in_months}}" id="period_in_months" required/>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="purpose">Loan Purpose</label>
                                    <input type="text" class="form-control pb-0 mt-2" name="purpose" value="{{$advanceApplication->purpose}}" id="purpose"/>
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="id" value="{{$advanceApplication->id}}">
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Amend</button>
                        </div>

                    </form>
                    {{--hidden fields--}}

                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>


@endsection