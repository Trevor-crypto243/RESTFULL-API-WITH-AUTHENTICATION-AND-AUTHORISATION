@extends('layouts.app')
@section('title', 'Loan Details')
@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#repayments-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-loan-repayments', $loan->id) }}', // the route to be called via ajax
                {{--ajax: '{{ url('ajax/bms/readings/get/'. $bms->imei) }}', // the route to be called via ajax--}}
                columns: [ // datatable columns
                    {data: 'transaction_receipt_number', name: 'transaction_receipt_number'},
                    {data: 'amount_repaid', name: 'amount_repaid'},
                    {data: 'outstanding_balance', name: 'outstanding_balance'},
                    {data: 'payment_channel', name: 'payment_channel'},
                    {data: 'description', name: 'description'},
                    {data: 'created_at', name: 'created_at'},
                    // {data: 'actions', name: 'actions'}
                ],
                // columnDefs: [
                //     { searchable: false, targets: [5] },
                //     { orderable: false, targets: [5] }
                // ],
                "pagingType": "full_numbers",
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search Repayments",
                },
                order: [[1, 'desc']]
            });//end datatable


            $('#payment-schedule-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-loan-schedule', $loan->id) }}', // the route to be called via ajax
                {{--ajax: '{{ url('ajax/bms/readings/get/'. $bms->imei) }}', // the route to be called via ajax--}}
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'payment_date', name: 'payment_date'},
                    {data: 'beginning_balance', name: 'beginning_balance'},
                    {data: 'scheduled_payment', name: 'scheduled_payment'},
                    {data: 'interest_paid', name: 'interest_paid'},
                    {data: 'principal_paid', name: 'principal_paid'},
                    {data: 'ending_balance', name: 'ending_balance'},
                    // {data: 'actions', name: 'actions'}
                ],
                // columnDefs: [
                //     { searchable: false, targets: [5] },
                //     { orderable: false, targets: [5] }
                // ],
                "pagingType": "full_numbers",
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search Schedule",
                },
                // order: [[1, 'desc']]
            });//end datatable

        });


        $('.approve-loan-form').on('submit', function() {
            if (confirm('Are you sure you want to approve this loan?')) {
                return true;
            }
            return false;
        });

    </script>
@endpush

@section('content')
    <div class="container-fluid" style="margin-top: -50px">


        <div class="row">
            <div class="col-md-10 ml-auto mr-auto">
                <div class="page-categories">
                    <h3 class=" text-center">Loan Details</h3>
                    <br />
                    <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#link7" role="tablist">
                                <i class="material-icons">info</i> Loan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link9" role="tablist">
                                <i class="material-icons">list</i> Payment Schedule
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link8" role="tablist">
                                <i class="material-icons">payments</i> Payments
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
                                    <h4 class="card-title">{{$loan->user->name}} - <small class="category">Loan details</small>
                                        <a href="{{url('customers/details',optional(\App\CustomerProfile::where('user_id', $loan->user_id)->first())->id)}}" class="btn btn-info btn-sm">
                                            View User Profile
                                        </a>

                                        @if ($loan->approval_status == 'APPROVED' && ($loan->repayment_status == 'PENDING' || $loan->repayment_status == 'PARTIALLY_PAID'))
                                            <button class="btn btn-success btn-sm add-btn" data-toggle="modal" data-target="#repay-modal">
                                                Repay From wallet
                                            </button>
                                        @endif

                                    </h4>

                                </div>
                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-md-4">


                                            {{--                                            <h6 class="card-category text-gray">LOAN</h6>--}}
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>
                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Requested Amount:</td>
                                                    <td style="text-align: left">KES {{number_format($loan->amount_requested)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Total Fees:</td>
                                                    <td style="text-align: left">KES {{number_format($loan->fees)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Amount disburseable:</td>
                                                    <td style="text-align: left">KES {{number_format($loan->amount_disbursable)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Period:</td>
                                                    <td style="text-align: left">{{$loan->period_in_months}} months</td>
                                                </tr>



                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Approval status:</td>
                                                    <td style="text-align: left">
                                                        @if ($loan->approval_status == 'PENDING')
                                                            <span class="badge pill badge-info">{{$loan->approval_status}}</span>
                                                        @elseif ($loan->approval_status == 'APPROVED')
                                                            <span class="badge pill badge-success">{{$loan->approval_status}}</span>
                                                        @elseif ($loan->approval_status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{$loan->approval_status}}</span>
                                                        @else
                                                            <span class="badge pill badge-info">{{$loan->approval_status}}</span>
                                                        @endif

                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Repayment status:</td>
                                                    <td style="text-align: left">
                                                        @if ($loan->repayment_status == 'PENDING')
                                                            <span class="badge pill badge-info">{{$loan->repayment_status}}</span>
                                                        @elseif ($loan->repayment_status == 'APPROVED')
                                                            <span class="badge pill badge-success">{{$loan->repayment_status}}</span>
                                                        @elseif ($loan->repayment_status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{$loan->repayment_status}}</span>
                                                        @else
                                                            <span class="badge pill badge-primary">{{$loan->repayment_status}}</span>
                                                        @endif
                                                    </td>
                                                </tr>



                                                </tbody>

                                            </table>
                                        </div>

                                        <div class="col-md-4">
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Loan Product:</td>
                                                    <td style="text-align: left"> {{optional($loan->product)->name}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Interest Rate:</td>
                                                    <td style="text-align: left"> {{$loan->interest_rate}} %</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Approved on.:</td>
                                                    <td style="text-align: left">{{\Carbon\Carbon::parse($loan->created_at)->isoFormat('MMMM Do YYYY, hh:mm:ss')}}</td>
                                                </tr>

{{--                                                <tr>--}}
{{--                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Approved on.:</td>--}}
{{--                                                    <td style="text-align: left">{{$loan->approved_date == null ? '' : \Carbon\Carbon::parse($loan->approved_date)->isoFormat('MMMM Do YYYY, hh:mm:ss')}}</td>--}}
{{--                                                </tr>--}}

                                                @if($loan->approval_status == 'REJECTED')
                                                    <tr>
                                                        <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Reject Reason:</td>
                                                        <td style="text-align: left">{{$loan->reject_reason}}</td>
                                                    </tr>
                                                @endif()


                                                </tbody>

                                            </table>

                                            @if(auth()->user()->role->has_perm([12]))
                                                @if($loan->approval_status == 'PENDING')
                                                    <div class="row mt-1">
                                                        <div class="col-md-6">
                                                            <form action="{{ url('loans/action/approve') }}" method="post" style="display: inline;" class="approve-loan-form">
                                                                {{ csrf_field() }}
                                                                <input type="hidden" name="loan_id" value="{{$loan->id}}">
                                                                <button class="btn btn-success btn-sm">Approve Loan</button>
                                                            </form>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-modal">
                                                                Reject Loan
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif()
                                            @endif

                                        </div>

                                        <div class="col-md-4">
                                            <strong class="category">Applied Fees</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <thead >
                                                <tr>
                                                    <th>Fee</th>
                                                    <th>Amount</th>
                                                    <th>Freq.</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($loan->request_fees as $request_fee)
                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">{{$request_fee->fee}}</td>
                                                        <td style="padding: 0px; margin: 0px">KES {{number_format($request_fee->amount)}}</td>
                                                        <td style="padding: 0px; margin: 0px">{{$request_fee->frequency}}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{--PAYMENTS--}}
                        <div class="tab-pane" id="link8">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">payments</i>
                                    </div>
                                    <h4 class="card-title">Payments -
                                        <small class="category">Loan payments history</small>
                                    </h4>
                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="repayments-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>TRX. NO.</th>
                                                <th>AMOUNT</th>
                                                <th>BALANCE</th>
                                                <th>CHANNEL</th>
                                                <th>DESCRIPTION</th>
                                                <th>CREATED AT</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>TRX. NO.</th>
                                                <th>AMOUNT</th>
                                                <th>BALANCE</th>
                                                <th>CHANNEL</th>
                                                <th>DESCRIPTION</th>
                                                <th>CREATED AT</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
                                    </div>
                                </div>
                            </div>

                        </div>


                        {{--PAYMENT SCHEDULE--}}
                        <div class="tab-pane" id="link9">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">list</i>
                                    </div>
                                    <h4 class="card-title">Payment Schedule -
                                        <small class="category">Monthly payment schedule</small>
                                    </h4>
                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="payment-schedule-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>DATE</th>
                                                <th>BEGINNING BALANCE</th>
                                                <th>SCHEDULED PAYMENT</th>
                                                <th>INTEREST PAID</th>
                                                <th>PRINCIPAL PAID</th>
                                                <th>ENDING BALANCE</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>ID</th>
                                                <th>DATE</th>
                                                <th>BEGINNING BALANCE</th>
                                                <th>SCHEDULED PAYMENT</th>
                                                <th>INTEREST PAID</th>
                                                <th>PRINCIPAL PAID</th>
                                                <th>ENDING BALANCE</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
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
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Reject </span> Loan</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('loans/action/reject') }}" method="post" id="user-form" enctype="multipart/form-data">
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

                        <input type="hidden" name="loan_id" value="{{$loan->id}}">


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

    <div class="modal fade" id="repay-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"> Repay loan from user's wallet</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('loans/repay/wallet') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <input type="hidden" name="loan_id" value="{{$loan->id}}">

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Outstanding Loan Balance: Ksh {{number_format($loanBalance)}}</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Available wallet balance: Ksh {{number_format($loan->user->wallet->current_balance)}}</h5>
                            </div>
                        </div>



                        <div class="row mt-5">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="min_amount">Amount to repay</label>
                                    <input type="number" value="{{ old('amount') }}" class="form-control" id="amount" name="amount" required />
                                </div>
                            </div>
                        </div>


                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close"></i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="fa fa-save"></i> Pay</button>
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
