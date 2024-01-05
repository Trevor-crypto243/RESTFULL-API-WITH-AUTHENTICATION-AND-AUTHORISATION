@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="container">

    <div class="row">
        <div class="col-lg-12">
            @include('layouts.common.success')
            @include('layouts.common.warnings')
            @include('layouts.common.warning')
        </div>

        @if($employer->salary_advance == true)
            @if(auth()->user()->user_group == 2)
                {{--HR user--}}
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats" id="total_customers" style="padding-bottom: 20px">
                        <div class="card-header card-header-primary card-header-icon">
                            <div class="card-icon" style="margin-right: 0px; padding: 10px">
                                <i class="material-icons">supervised_user_circle</i>
                            </div>
                            <p class="card-category">Total Employees</p>
                            <h3 class="card-title">{{$total}}</h3>
                        </div>

                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats" id="checkoff_customers" style="padding-bottom: 20px">
                        <div class="card-header card-header-icon card-header-icon">
                            <div class="card-icon" style="margin-right: 0px; padding: 10px">
                                <i class="material-icons">how_to_reg</i>
                            </div>
                            <p class="card-category">Total Requests</p>
                            <h3 class="card-title">{{$totalAdvanceRequests}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats" id="approved_today" style="padding-bottom: 20px">
                        <div class="card-header card-header-success card-header-icon">
                            <div class="card-icon" style="margin-right: 0px; padding: 10px">
                                <i class="material-icons">done_all</i>
                            </div>
                            <p class="card-category">Approved Requests</p>
                            <h3 class="card-title">{{$approvedAdvanceRequests}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="paid_today" style="padding-bottom: 20px">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">autorenew</i>
                        </div>
                        <p class="card-category">Pending Requests</p>
                        <h3 class="card-title">{{$pendingAdvanceRequests}}</h3>
                    </div>

                </div>
            </div>
            @endif
        @endif


        @if($employer->invoice_discounting == true)
            @if(auth()->user()->user_group == 5)
                {{--acounts manager--}}
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats" id="total_customers" style="padding-bottom: 20px">
                        <div class="card-header card-header-primary card-header-icon">
                            <div class="card-icon" style="margin-right: 0px; padding: 10px">
                                <i class="material-icons">supervised_user_circle</i>
                            </div>
                            <p class="card-category">Total Invoices</p>
                            <h3 class="card-title">{{$totalInvoices}}</h3>
                        </div>

                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats" id="checkoff_customers" style="padding-bottom: 20px">
                        <div class="card-header card-header-icon card-header-icon">
                            <div class="card-icon" style="margin-right: 0px; padding: 10px">
                                <i class="material-icons">how_to_reg</i>
                            </div>
                            <p class="card-category">Pending Invoices</p>
                            <h3 class="card-title">{{$pendingInvoices}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats" id="approved_today" style="padding-bottom: 20px">
                        <div class="card-header card-header-success card-header-icon">
                            <div class="card-icon" style="margin-right: 0px; padding: 10px">
                                <i class="material-icons">done_all</i>
                            </div>
                            <p class="card-category">Approved Invoices</p>
                            <h3 class="card-title">{{$approvedInvoices}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card card-stats" id="paid_today" style="padding-bottom: 20px">
                        <div class="card-header card-header-info card-header-icon">
                            <div class="card-icon" style="margin-right: 0px; padding: 10px">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <p class="card-category">Paid Invoices</p>
                            <h3 class="card-title">{{$paidInvoices}}</h3>
                        </div>

                    </div>
                </div>
            @endif

        @endif
    </div>

    <div class="row ">
        @if($employer->salary_advance == true)
            @if(auth()->user()->user_group == 2)
                {{--HR user--}}
                <div class="col-md-6">
                    <div class="card">

                        <div class="card-header">
                            <div id = "container">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Recent Salary Advance Applications</strong>
                                    </div>

                                    <div class="col-md-6">
                                        <a href="{{url('hr/advance/pending')}}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-eye"></i> View All requests
                                        </a>
                                    </div>

                                    {{--                        <div class="col-md-4">--}}
                                    {{--                            <strong>B2C Balances (3028315)</strong>--}}
                                    {{--                        </div>--}}

                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Payroll No.</th>
                                    <th>Amount Requested</th>
                                    <th>Action</th>
                                    </thead>
                                    <tbody>
                                    @foreach($recent as $inuaApplication)

                                        <tr>
                                            <td>
                                                <a href="{{optional(\App\Employee::where('user_id', $inuaApplication->user_id)->where('employer_id', $hr->employer_id)->first())->passport_photo_url}}" target="_blank">
                                                    <img src="{{optional(\App\Employee::where('user_id', $inuaApplication->user_id)->where('employer_id', $hr->employer_id)->first())->passport_photo_url}}" width="75" height="75" />
                                                </a>
                                            </td>

                                            <td>
                                                {{$inuaApplication->user->name}}
                                            </td>

                                            <td>
                                                {{optional(\App\Employee::where('user_id', $inuaApplication->user_id)->where('employer_id', $hr->employer_id)->first())->payroll_no}}
                                            </td>
                                            <td>
                                                KES {{number_format($inuaApplication->amount_requested)}}
                                            </td>

                                            <td class="text-right">
                                                <a href="{{route('advance-hr-application-details' ,  $inuaApplication->id)}}"
                                                   class="btn btn-primary btn-link btn-sm">
                                                    <i class="material-icons">visibility</i> View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        @endif


        @if($employer->invoice_discounting == true)
                @if(auth()->user()->user_group == 5)
                    {{--acounts manager--}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header ">
                                <div id = "container">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Recent Invoices</strong>
                                        </div>

                                        <div class="col-md-6">
                                            <a href="{{url('manager/invoices')}}" class="btn btn-primary btn-sm">
                                                <i class="fa fa-eye"></i> View All requests
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        <th>Company</th>
                                        <th>Invoices</th>
                                        <th>Amount</th>
                                        <th></th>
                                        </thead>
                                        <tbody>

                                        @foreach($recentInvoices as $recentInvoice)
                                            <tr>

                                                <td>
                                                    {{optional($recentInvoice->company)->business_name}}
                                                </td>

                                                <td>
                                                    {{$recentInvoice->invoices->count()}}
                                                </td>

                                                <td>
                                                    KES {{number_format($recentInvoice->invoices->sum('invoice_amount'))}}
                                                </td>


                                                <td class="text-right">
                                                    <a href="{{route('manager-invoice-details',  $recentInvoice->id)}}"
                                                       class="btn btn-primary btn-link btn-sm">
                                                        <i class="material-icons">visibility</i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
        @endif

    </div>
</div>
@endsection
