@extends('layouts.app')
@section('title', 'Application Details')
@push('js')
    <script>

        $(function() {
            // server side - lazy loading
            $('#invoices-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-id-invoices', $invoiceDiscount->id) }}', // the route to be called via ajax
                {{--ajax: '{{ url('ajax/bms/readings/get/'. $bms->imei) }}', // the route to be called via ajax--}}
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'invoice_amount', name: 'invoice_amount'},
                    {data: 'invoice_number', name: 'invoice_number'},
                    {data: 'grn_no', name: 'grn_no'},
                    {data: 'lpo_no', name: 'lpo_no'},
                    {data: 'invoice_branch', name: 'invoice_branch'},
                    {data: 'invoice_date', name: 'invoice_date'},
                    {data: 'expected_payment_date', name: 'expected_payment_date'},
                    {data: 'approval_status', name: 'approval_status'},
                    {data: 'actions', name: 'actions'}
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
                    searchPlaceholder: "Search Invoice",
                },
                order: [[1, 'desc']]
            });//end datatable

            $(document).on('click', '.approve-invoice-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id');

                $('#approve_id').val(_id);
                $('#approve-modal').modal('show');
            });

            $(document).on('click', '.reject-invoice-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id');

                $('#reject_id').val(_id);
                $('#reject-modal').modal('show');
            });


            var _ModalTitle = $('#modal-title'),
                _SpoofInput = $('#spoof-input');

            $(document).on('click', '.edit-invoice-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id'),
                    _Form = $('#invoice-form');

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
                            $('#invoice_amount').val(data['invoice_amount']);
                            $('#invoice_date').val(data['invoice_date']);
                            $('#grn_no').val(data['grn_no']);
                            $('#lpo_no').val(data['lpo_no']);
                            $('#invoice_number').val(data['invoice_number']);
                            $('#id').val(data['id']);

                            // set the update url
                            var action =  _Form .attr('action');
                            // action = action + '/' + season_id;
                            console.log(action);
                            _Form .attr('action', action);

                            // open the modal
                            $('#edit-modal').modal('show');
                        }
                    });
                }
            });



            $('.submit-for-approval').on('submit', function() {
                if (confirm('Are you sure you want to submit this invoice discount for approval?')) {
                    return true;
                }
                return false;
            });

            $('.submit-for-admin-approval').on('submit', function() {
                if (confirm('Are you sure you want to submit this IDF for admin approval?')) {
                    return true;
                }
                return false;
            });

        });

        // $('.approve-loan-form').on('submit', function() {
        //     if (confirm('Are you sure you want to approve this request?')) {
        //         return true;
        //     }
        //     return false;
        // });

        // $('.send-to-hr-form').on('submit', function() {
        //     if (confirm('Are you sure you want to send this request to HR for approval?')) {
        //         return true;
        //     }
        //     return false;
        // });

    </script>
@endpush

@section('content')
    <div class="container-fluid" style="margin-top: -50px">


        <div class="row">
            <div class="col-md-12">
                <div class="page-categories">
                    <h3 class=" text-center">Invoice Discount Details</h3>
                    <br />
                    <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#link7" role="tablist">
                                <i class="material-icons">info</i> Invoice Discount
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link8" role="tablist">
                                <i class="material-icons">receipt</i> Invoices
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
                                        <div class="col-md-8">
                                            <h4 class="card-title">

                                                {{optional($invoiceDiscount->company)->business_name}} - {{$invoiceDiscount->invoices->count()}} Invoices

                                            @if($invoiceDiscount->invoices->count() > 0)
                                                    <a href="{{url('invoices/list',$invoiceDiscount->id)}}" class="btn btn-success btn-sm ml-3">
                                                        Download invoice list
                                                    </a>
                                                @endif
                                            </h4>
                                        </div>
                                        <div class="col-md-4">
                                            @if($invoiceDiscount->offer_status == 'PENDING' && $invoiceDiscount->Quicksava_submitted_by != null)
                                                @if(auth()->user()->role->has_perm([21]))
                                                    <h4 class="card-title">
                                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#send-modal">
                                                            Send Offer
                                                        </button>
                                                    </h4>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="col-md-4">
                                            @if($invoiceDiscount->offer_status == 'PENDING' && $invoiceDiscount->Quicksava_submitted_by == null)
                                                @if(auth()->user()->role->has_perm([24]) && auth()->user()->user_group != 1)
                                                    <h4 class="card-title">
                                                        <form action="{{url('invoices/invoice_discount/submit_approval')}}" style="display: inline;" method="post" class="submit-for-approval">
                                                            <input type="hidden" name="invoice_discount_id" value="{{$invoiceDiscount->id}}">
                                                            @csrf()
                                                            <button class="btn btn-success btn-sm" >Submit for approval</button>
                                                        </form>
                                                    </h4>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="col-md-4">
                                            @if($invoiceDiscount->offer_status == 'PENDING' && $invoiceDiscount->admin_approval_submitted_by == null)
                                                @if(auth()->user()->role->has_perm([23]) && auth()->user()->user_group != 1)
                                                    <h4 class="card-title">
                                                        <form action="{{url('invoices/invoice_discount/submit')}}" style="display: inline;" method="post" class="submit-for-admin-approval">
                                                            <input type="hidden" name="invoice_discount_id" value="{{$invoiceDiscount->id}}">
                                                            @csrf()
                                                            <button class="btn btn-success btn-sm" >Submit for admin approval</button>
                                                        </form>
                                                    </h4>
                                                @endif
                                            @endif
                                        </div>

                                    </div>

                                    <a href="{{url('companies/details',$invoiceDiscount->company_id)}}" class="btn btn-success btn-sm ml-3">
                                        View Company
                                    </a>
                                </div>
                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-md-4">


                                            {{--                                            <h6 class="card-category text-gray">LOAN</h6>--}}
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>
                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Invoice Amount:</td>
                                                    <td style="text-align: left">KES {{number_format($invoiceDiscount->invoices->sum('invoice_amount'))}}</td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Approved Amount:</td>
                                                    <td style="text-align: left">KES {{number_format($invoiceDiscount->approved_amount)}}</td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Merchant:</td>
                                                    <td style="text-align: left">{{optional($invoiceDiscount->employer)->business_name}}</td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Date Created:</td>
                                                    <td style="text-align: left">{{\Carbon\Carbon::parse($invoiceDiscount->created_at)->isoFormat('MMM Do YYYY HH:mm:ss')}}</td>
                                                    <td style="text-align: left"></td>

                                                </tr>



                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Offer status:</td>
                                                    <td style="text-align: left">
                                                        @if ($invoiceDiscount->offer_status == 'PENDING')
                                                            <span class="badge pill badge-info">{{$invoiceDiscount->offer_status}}</span>
                                                        @elseif ($invoiceDiscount->offer_status == 'AVAILABLE')
                                                            <span class="badge pill badge-warning">{{$invoiceDiscount->offer_status}}</span>
                                                        @elseif ($invoiceDiscount->offer_status == 'ACCEPTED')
                                                            <span class="badge pill badge-success">{{$invoiceDiscount->offer_status}}</span>
                                                        @elseif ($invoiceDiscount->offer_status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{$invoiceDiscount->offer_status}}</span>
                                                        @endif

                                                    </td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Payment status:</td>
                                                    <td style="text-align: left">
                                                        @if ($invoiceDiscount->payment_status == 'PENDING')
                                                            <span class="badge pill badge-info">{{$invoiceDiscount->payment_status}}</span>
                                                        @elseif ($invoiceDiscount->payment_status == 'CANCELLED')
                                                            <span class="badge pill badge-warning">{{$invoiceDiscount->payment_status}}</span>
                                                        @elseif ($invoiceDiscount->payment_status == 'PAID')
                                                            <span class="badge pill badge-success">{{$invoiceDiscount->payment_status}}</span>
                                                        @endif

                                                    </td>
                                                    <td style="text-align: left"></td>
                                                </tr>

                                                </tbody>

                                            </table>

                                        </div>
                                        <div class="col-md-4">
                                            <strong class="category">{{optional($invoiceDiscount->company)->business_name}}</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <tbody>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Type</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{optional($invoiceDiscount->company)->type}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Status</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        @if (optional($invoiceDiscount->company)->status == 'ACTIVE')
                                                            <span class="badge pill badge-success">{{optional($invoiceDiscount->company)->status}}</span>
                                                        @elseif (optional($invoiceDiscount->company)->status == 'PENDING')
                                                            <span class="badge pill badge-info">{{optional($invoiceDiscount->company)->status}}</span>
                                                        @elseif (optional($invoiceDiscount->company)->status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{optional($invoiceDiscount->company)->status}}</span>
                                                        @elseif (optional($invoiceDiscount->company)->status == 'INCOMPLETE')
                                                            <span class="badge pill badge-warning">{{optional($invoiceDiscount->company)->status}}</span>
                                                        @endif

                                                    </td>
                                                </tr>



                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">KRA PIN</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        <a href="{{ optional($invoiceDiscount->company)->tax_pin_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> {{optional($invoiceDiscount->company)->tax_pin}} </a>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Tax Compliance</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        <a href="{{ optional($invoiceDiscount->company)->tax_compliance_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View </a>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Reg. Number</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        <a href="{{ optional($invoiceDiscount->company)->registration_certificate_url }}" class="btn btn-primary btn-link btn-sm" target="_blank">
                                                            {{optional($invoiceDiscount->company)->reg_no}}
                                                        </a>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Phone No.:</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{ optional(optional($invoiceDiscount->company)->owner)->phone_no }}
                                                    </td>
                                                </tr>

                                                @if(optional($invoiceDiscount->company)->type == 'LIMITED COMPANY')
                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">Articles of Assoc.</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ optional($invoiceDiscount->company)->articles_url }}" class="btn btn-primary btn-link btn-sm" target="_blank">
                                                                View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">CR12.</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ optional($invoiceDiscount->company)->cr12_url }}" class="btn btn-primary btn-link btn-sm" target="_blank">
                                                                View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endif

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Irrevocable Letter</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        @if(optional($invoiceDiscount->company)->irrevocable_letter == null)
                                                            <a href="#" class="btn btn-primary btn-link btn-sm" >  Not Available </a>
                                                        @else
                                                            <a href="{{ optional($invoiceDiscount->company)->irrevocable_letter }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View </a>
                                                        @endif
                                                    </td>
                                                </tr>


                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-4">
                                            <strong class="category">Offer Details</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <tbody>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Product</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        @if($invoiceDiscount->loan_product_id !=null)
                                                            {{$invoiceDiscount->product->name}}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Amount</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        Ksh. {{number_format(0.8*$invoiceDiscount->approved_amount,2)}}
                                                    </td>
                                                </tr>


                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Interest rate</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{$interestRate}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Period (months)</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{$periodInMonths}}
                                                    </td>
                                                </tr>

{{--                                                <tr>--}}
{{--                                                    <td style="padding: 0px; margin: 0px">Total Fees</td>--}}
{{--                                                    <td style="padding: 0px; margin: 0px">--}}

{{--                                                    </td>--}}
{{--                                                </tr>--}}


                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <strong class="category">Directors:</strong>

                                            <table class="table table-no-bordered table-hover">
                                                <thead>
                                                <tr>
                                                    <td></td>
                                                    <td><strong>NAME</strong></td>
                                                    <td><strong>ID. NO</strong></td>
                                                    <td><strong>ID. PHOTO</strong></td>
                                                    <td><strong>KRA PIN</strong></td>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                @foreach(\App\CompanyDirector::where('company_id', $invoiceDiscount->company_id)->get() as $director)
                                                    <tr>
                                                        <td style=" ">
                                                            <img src="{{$director->passport_photo_url}}" width="100dp" height="100dp" alt="{{$director->name}}">
                                                        </td>
                                                        <td style="padding: 0px; margin: 0px">{{$director->name}}</td>
                                                        <td style="padding: 0px; margin: 0px">{{$director->id_no}}</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ $director->id_front_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View Front </a>
                                                            <a href="{{ $director->id_back_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View Back </a>
                                                        </td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{$director->tax_pin_url}}" target="_blank">
                                                                {{$director->tax_pin}}
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
                        </div>

                        {{--INVOICES--}}
                        <div class="tab-pane" id="link8">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">receipt</i>
                                    </div>
                                    <h4 class="card-title">Invoices - ({{$invoiceDiscount->invoices->count()}})
                                        <small class="category">Verify and approve each invoice</small>

                                        @if($invoiceDiscount->invoices->count() > 0)
                                            <a href="{{url('invoices/list',$invoiceDiscount->id)}}" class="btn btn-success btn-sm ml-3">
                                                Download invoice list
                                            </a>
                                        @endif
                                    </h4>
                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="invoices-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Amount</th>
                                                <th>Inv. Number</th>
                                                <th>GRN No.</th>
                                                <th>LPO No.</th>
                                                <th>Inv. Branch</th>
                                                <th>Inv. Date</th>
                                                <th>Payment Date</th>
                                                <th>Approval</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Amount</th>
                                                <th>Inv. Number</th>
                                                <th>GRN No.</th>
                                                <th>LPO No.</th>
                                                <th>Inv. Branch</th>
                                                <th>Inv. Date</th>
                                                <th>Payment Date</th>
                                                <th>Approval</th>
                                                <th>Action</th>
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
    <div class="modal fade" id="send-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Send </span> offer to supplier</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('invoices/invoice_discount/send') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Loan Product" tabindex="-98"
                                            name="loan_product_id" id="loan_product_id" required>
                                        @foreach( \App\LoanProduct::all() as $loanProduct)
                                            <option value="{{ $loanProduct->id  }}">{{ $loanProduct->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="invoice_discount_id" value="{{$invoiceDiscount->id}}">


                        <div class="form-group">
                            <button class="btn btn-success btn-block" id="save-brand"><i class="material-icons">save</i> Send Offer</button>
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

    <div class="modal fade" id="approve-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Approve </span> invoice</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('invoices/requests/approve') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="name">Expected invoice payment date</label>
                                    <input type="text" value="{{ old('expected_payment_date') }}" class="form-control date datepicker" id="expected_payment_date" name="expected_payment_date" required />
                                </div>
                            </div>

                        </div>

                        <input type="hidden" name="invoice_id" id="approve_id">


                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Approve</button>
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

    <div class="modal fade" id="reject-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Reject </span> invoice</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('invoices/requests/reject') }}" method="post" id="user-form" enctype="multipart/form-data">
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

                        <input type="hidden" name="invoice_id" id="reject_id">


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

    <div class="modal fade" id="edit-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="modal-title">Edit </span> Invoice</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('invoice_discounts/update_invoice') }}" method="post" id="invoice-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_amount">Amount</label>
                                    <input type="number" class="form-control" id="invoice_amount" name="invoice_amount" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="invoice_number">Inv. Number</label>
                                    <input type="text" class="form-control pb-0 mt-2" name="invoice_number" id="invoice_number" required/>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="grn_no">GRN Number</label>
                                    <input type="text"  class="form-control pb-0 mt-2" name="grn_no" id="grn_no"/>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="lpo_no">LPO Number</label>
                                    <input type="text"  class="form-control pb-0 mt-2" name="lpo_no" id="lpo_no"/>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="lpo_no">Invoice Date</label>
                                    <input type="text"  class="form-control pb-0 mt-2 datepicker" name="invoice_date" id="invoice_date"/>
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

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>



@endsection
