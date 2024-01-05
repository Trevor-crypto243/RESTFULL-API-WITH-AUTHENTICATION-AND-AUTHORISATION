@extends('layouts.app')
@section('title', 'Application Details')
@push('js')
    <script>

        $(function() {
            // server side - lazy loading
            $('#vehicles-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('logbook-vehicles-dt', $application->id) }}', // the route to be called via ajax
                {{--ajax: '{{ url('ajax/bms/readings/get/'. $bms->imei) }}', // the route to be called via ajax--}}
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'make', name: 'make'},
                    {data: 'model', name: 'model'},
                    {data: 'yom', name: 'yom'},
                    {data: 'reg_no', name: 'reg_no'},
                    {data: 'chassis_no', name: 'chassis_no'},
                    {data: 'actions', name: 'actions'},
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
                    searchPlaceholder: "Search Vehicles",
                },
                order: [[1, 'desc']]
            });//end datatable



            let changeCount = 0;
            let initialModelId = 0;

            var _ModalTitle = $('#vehicle-modal-title'),
                _SpoofInput = $('#vehicle-spoof-input'),
                _Form = $('#vehicle-form');

            // edit
            $(document).on('click', '.edit-vehicle-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id'),
                    _Form = $('#vehicle-form');

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
                            // console.log(data);
                            // populate the modal fields using data from the server
                            $('#yom').val(data['yom']);
                            $('#reg_no').val(data['reg_no']);
                            $('#chassis_no').val(data['chassis_no']);
                            $('#insurance_company').val(data['insurance_company']);
                            $('#insurance_expiry_date').val(data['insurance_expiry_date']);
                            $('#premium_amount_paid').val(data['premium_amount_paid']);
                            $('#forced_sale_value').val(data['forced_sale_value']);
                            $('#market_value').val(data['market_value']);
                            $('#valuation_date').val(data['valuation_date']);

                            $("#logbook_link").attr("href", data['logbook_url'])
                            $("#icf_link").attr("href", data['icf_confirmation_form_url'])
                            $("#valuation_link").attr("href", data['valuation_report_url'])


                            $('#make_id').val(data['vehicle_make_id']).change();
                            initialModelId = data['vehicle_model_id'];
                            $('#premium_paid_by').val(data['premium_paid_by']).change();

                            $('#id').val(data['id']);

                            // set the update url
                            var action =  _Form.attr('action');
                            // action = action + '/' + season_id;
                            // console.log(action);
                            _Form .attr('action', action);

                            changeCount = 0;

                            // open the modal
                            $('#vehicle-modal').modal('show');
                        }
                    });
                }
            });

            $('#make_id').on('change', function() {
                $('#model_id').empty();

                $.ajax({
                    url: '/auto/models/json/'+this.value,
                    dataType: 'JSON',
                    type: 'GET',
                    success: function(response) {
                        //console.log(response);

                        var len = response.length;

                        $('#model_id').append( '<option value="">--Select Model--</option>' );


                        for( var i = 0; i<len; i++){
                            var id = response[i]['id'];
                            var name = response[i]['model'];

                            // console.log("<option value='"+id+"'>"+name+"</option>");


                            $('#model_id').append( '<option value="'+id+'">'+name+'</option>' );

                        }

                        if (changeCount === 0 ){
                            $('#model_id').val(initialModelId).change();
                        }

                        changeCount++;

                    }
                })
            });

        });



        $('.submit-for-review').on('submit', function() {
            if (confirm('Are you sure you want to submit this request for review?')) {
                return true;
            }
            return false;
        });

        $('.submit-for-approval').on('submit', function() {
            if (confirm('Are you sure you want to submit this request for approval?')) {
                return true;
            }
            return false;
        });

        $('.delete-deduction-form').on('submit', function() {
            if (confirm('Are you sure you want to delete this deduction?')) {
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
                    <h3 class=" text-center">Logbook Application Details</h3>
                    <br />
                    <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#link7" role="tablist">
                                <i class="material-icons">info</i> Application
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link8" role="tablist">
                                <i class="material-icons">directions_car</i> Vehicles
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link9" role="tablist">
                                <i class="material-icons">receipt</i> Deductions
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
                                        <div class="col-md-12">
                                            <h4 class="card-title">

                                                {{ $application->applicant_type == 'INDIVIDUAL' ? $application->user->surname.' '.$application->user->name : $application->company_name}} - {{$application->vehicles->count()}} Vehicles

                                                @if($application->status == 'IN REVIEW')

                                                    {{--Edit buttons--}}
                                                    @if(auth()->user()->role->has_perm([30]))
                                                        @if($application->applicant_type == 'INDIVIDUAL')
                                                            @if(!($application->submitted_for_review_by == auth()->user()->id || $application->submitted_for_approval_by == auth()->user()->id))
                                                                <button class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#edit-individual-modal">
                                                                    Edit Application
                                                                </button>
                                                            @endif
                                                        @endif

                                                        @if($application->applicant_type == 'COMPANY')
                                                            @if(!($application->submitted_for_review_by == auth()->user()->id || $application->submitted_for_approval_by == auth()->user()->id))
                                                                 <button class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#edit-company-modal">
                                                                    Edit Application
                                                                 </button>
                                                            @endif
                                                        @endif
                                                    @endif

                                                    {{--Leave comments--}}
                                                    @if(auth()->user()->role->has_perm([31]))
                                                        @if($application->submitted_for_review_by != auth()->user()->id && $application->submitted_for_approval_by != auth()->user()->id)
                                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#comment-modal">
                                                                Leave Comment
                                                            </button>
                                                        @endif
                                                    @endif

                                                    {{--Submit for review--}}
                                                    @if(auth()->user()->role->has_perm([33]) && auth()->user()->user_group != 1 )
                                                        @if($application->submitted_for_review_by == null)
                                                            <form action="{{url('auto/applications/submit/review')}}" style="display: inline;" method="post" class="submit-for-review">
                                                                <input type="hidden" name="id" value="{{$application->id}}">
                                                                @csrf()
                                                                <button class="btn btn-success btn-sm" >Submit for Review</button>
                                                            </form>
                                                        @endif
                                                    @endif

                                                    {{--Submit for approval--}}
                                                    @if(auth()->user()->role->has_perm([34]) && auth()->user()->user_group != 1)
                                                        @if($application->submitted_for_approval_by == null && $application->submitted_for_review_by != null)
                                                            <form action="{{url('auto/applications/submit/approval')}}" style="display: inline;" method="post" class="submit-for-approval">
                                                                <input type="hidden" name="id" value="{{$application->id}}">
                                                                @csrf()
                                                                <button class="btn btn-success btn-sm" >Submit for Approval</button>
                                                            </form>
                                                        @endif
                                                    @endif

                                                    {{--Approve and disburse--}}
                                                    @if(auth()->user()->role->has_perm([35]) && $application->submitted_for_review_by != null && $application->submitted_for_approval_by != null)
                                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#approve-modal">
                                                            Approve and Disburse
                                                        </button>
                                                    @endif

                                                    {{--Reject--}}
                                                    @if(auth()->user()->role->has_perm([36]))
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-modal">
                                                            Reject
                                                        </button>
                                                    @endif
                                                @endif

                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-md-4">


                                            {{--                                            <h6 class="card-category text-gray">LOAN</h6>--}}
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>
                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Requested Amount:</td>
                                                    <td style="text-align: left">KES {{number_format($application->requested_amount)}}</td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Approved Amount:</td>
                                                    <td style="text-align: left">KES {{number_format($application->approved_amount)}}</td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Period:</td>
                                                    <td style="text-align: left">{{$application->payment_period}} Months</td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Date Created:</td>
                                                    <td style="text-align: left">{{\Carbon\Carbon::parse($application->created_at)->isoFormat('MMM Do YYYY HH:mm:ss')}}</td>
                                                    <td style="text-align: left"></td>

                                                </tr>



                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">status:</td>
                                                    <td style="text-align: left">
                                                        @if ($application->status == 'NEW')
                                                            <span class="badge pill badge-default">{{$application->status}}</span>
                                                        @elseif ($application->status == 'IN REVIEW')
                                                            <span class="badge pill badge-primary">{{$application->status}}</span>
                                                        @elseif ($application->status == 'AMENDMENT')
                                                            <span class="badge pill badge-warning">{{$application->status}}</span>
                                                        @elseif ($application->status == 'OFFER')
                                                            <span class="badge pill badge-info">{{$application->status}}</span>
                                                        @elseif ($application->status == 'ACTIVE')
                                                            <span class="badge pill badge-primary">{{$application->status}}</span>
                                                        @elseif ($application->status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{$application->status}}</span>
                                                        @elseif ($application->status == 'PAID')
                                                            <span class="badge pill badge-success">{{$application->status}}</span>
                                                        @elseif ($application->status == 'CANCELLED')
                                                            <span class="badge pill badge-danger">{{$application->status}}</span>
                                                        @endif

                                                    </td>
                                                    <td style="text-align: left"></td>

                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Applicant Type:</td>
                                                    <td style="text-align: left">{{$application->applicant_type}}</td>
                                                    <td style="text-align: left"></td>
                                                </tr>


                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Source of Business:</td>
                                                    <td style="text-align: left">{{$application->source_of_business}} {{$application->lead_originator}}</td>
                                                    <td style="text-align: left"></td>
                                                </tr>

                                                </tbody>

                                            </table>
                                        </div>



                                        @if($application->applicant_type == 'COMPANY')
                                            <div class="col-md-4">
                                                <strong class="category">Applicant Details</strong>
                                                <table class="table table-no-bordered table-hover">
                                                    <tbody>

                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">Name</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                {{$application->company_name}}
                                                            </td>
                                                        </tr>


                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">KRA PIN</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                <a href="{{ $application->company_kra_pin_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> {{$application->company_kra_pin}} </a>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">Reg. Number</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                <a href="{{ $application->company_reg_no_url }}" class="btn btn-primary btn-link btn-sm" target="_blank">
                                                                    {{$application->company_reg_no}}
                                                                </a>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">Loan Form</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                @if($application->loan_form_url != null)
                                                                    <a href="{{$application->loan_form_url}}" class="btn btn-primary btn-link btn-sm" target="_blank">View Uploaded</a>
                                                                @else
                                                                    None Uploaded
                                                                @endif
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">Offer Letter</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                @if($application->offer_letter_url != null)
                                                                    <a href="{{$application->offer_letter_url}}" class="btn btn-primary btn-link btn-sm" target="_blank">View Uploaded</a>
                                                                @else
                                                                    None Uploaded
                                                                @endif
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">Directors</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                {{$application->directors }} Directors
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">Phone No.:</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                {{ optional($application->user)->phone_no }}
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td style="padding: 0px; margin: 0px">Payment Mode:</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                {{$application->payment_mode }}
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                                <strong class="category">Loan Purpose</strong>
                                                <p>
                                                    {{$application->loan_purpose}}
                                                </p>

                                            </div>
                                        @else
                                            <div class="col-md-4">
                                                <a href="{{$application->passport_photo_url}}" target="_blank">
                                                    <img src="{{$application->passport_photo_url}}" width="100%" height="200dp"
                                                         style="margin-bottom: 1rem;" alt="Selfie Photo">
                                                </a>
                                                <table class="table table-no-bordered table-hover">
                                                    <tbody>

                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">KRA PIN</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ $application->personal_kra_pin_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> {{$application->personal_kra_pin}} </a>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">ID</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ $application->id_front_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View Front </a>
                                                            <a href="{{ $application->id_back_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View Back </a>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">Loan Form</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            @if($application->loan_form_url != null)
                                                                <a href="{{$application->loan_form_url}}" class="btn btn-primary btn-link btn-sm" target="_blank">View Uploaded</a>
                                                            @else
                                                                None Uploaded
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">Offer Letter</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            @if($application->offer_letter_url != null)
                                                                <a href="{{$application->offer_letter_url}}" class="btn btn-primary btn-link btn-sm" target="_blank">View Uploaded</a>
                                                            @else
                                                                None Uploaded
                                                            @endif
                                                        </td>
                                                    </tr>


                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">Phone No.:</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            {{ optional($application->user)->phone_no }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">Payment Mode:</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            {{$application->payment_mode }}
                                                        </td>
                                                    </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif

                                        <div class="col-md-4">
                                            <strong class="category">Offer Details</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <tbody>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Loan Product</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        @if($application->loan_product_id !=null)
                                                            {{optional($application->product)->name}}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Loan Principal</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{number_format($loanPrincipal,2)}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Amount Disbursable</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        Ksh. {{number_format($amount_disbursable,2)}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Amount Payable</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        Ksh. {{number_format($amount_payable,2)}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Interest and tenure</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{$interestRate}} % for {{$application->payment_period}} Months
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Upfront Fees</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        KES. {{number_format($upfront_fees,2)}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Monthly Amount</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        Ksh. {{number_format($monthly_amount,2)}}
                                                    </td>
                                                </tr>

                                                </tbody>
                                            </table>

                                            @if($application->applicant_type == 'INDIVIDUAL')
                                                <strong class="category">Loan Purpose</strong>
                                                <p>
                                                    {{$application->loan_purpose}}
                                                </p>
                                            @endif

                                            @if($application->status == 'REJECTED')
                                                <strong class="category">Reject Reason</strong>
                                                <p>
                                                    {{$application->reject_reason}}
                                                </p>
                                            @endif

                                            @if($application->status == 'CANCELLED')
                                                <strong class="category">Cancellation Reason</strong>
                                                <p>
                                                    {{$application->cancellation_reason}}
                                                </p>
                                            @endif

                                            <strong class="category">Admin Comments:</strong>
                                            @foreach($adminComments as $adminComment)
                                                <p class="category">{{$adminComment->comment}} <br>
                                                    <i>{{$adminComment->user->name}} {{$adminComment->user->surname}} - {{ \Carbon\Carbon::parse($adminComment->created_at)->isoFormat('MMMM Do YYYY, hh:mm:ss')}}</i>
                                                </p>
                                            @endforeach


                                        </div>
                                    </div>

                                    @if($application->applicant_type == 'COMPANY')
                                        <div class="row">
                                            <div class="col-md-6">
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

                                                    @foreach(\App\LogbookCompanyDirector::where('logbook_loan_id', $application->id)->get() as $director)
                                                        <tr>
                                                            <td style="">
                                                                <img src="{{$director->passport_photo_url}}" width="100dp" height="100dp" alt="{{$director->first_name.' '.$director->surname}}">
                                                            </td>
                                                            <td style="padding: 0px; margin: 0px">{{$director->first_name.' '.$director->surname}}</td>
                                                            <td style="padding: 0px; margin: 0px">{{$director->id_no}}</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                <a href="{{ $director->id_front_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View Front </a>
                                                                <a href="{{ $director->id_back_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View Back </a>
                                                            </td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                <a href="{{$director->kra_pin_url}}" target="_blank">
                                                                    View KRA PIN
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="col-md-6">
                                                <strong class="category">Additional Files:</strong>
                                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#file-modal">
                                                    Upload File
                                                </button>

                                                <table class="table table-no-bordered table-hover">
                                                    <thead>
                                                    <tr>
                                                        <td><strong>FILENAME</strong></td>
                                                        <td><strong>UPLOADED BY</strong></td>
                                                        <td><strong>ACTION</strong></td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>

                                                    @foreach(\App\LogbookLoanAdditionalFile::where('logbook_loan_id', $application->id)->get() as $file)
                                                        <tr>

                                                            <td style="padding: 0px; margin: 0px">{{$file->file_name}}</td>
                                                            <td style="padding: 0px; margin: 0px">{{$file->user->name.' '.$file->user->surname}}</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                <a href="{{$file->file_url}}" target="_blank">
                                                                    View File
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong class="category">Additional Files:</strong>

                                                @if(auth()->user()->role->has_perm([32]))
                                                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#file-modal">
                                                        Upload File
                                                    </button>
                                                @endif

                                                <table class="table table-no-bordered table-hover">
                                                    <thead>
                                                    <tr>
                                                        <td><strong>FILENAME</strong></td>
                                                        <td><strong>UPLOADED BY</strong></td>
                                                        <td><strong>ACTION</strong></td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>

                                                    @foreach(\App\LogbookLoanAdditionalFile::where('logbook_loan_id', $application->id)->get() as $file)
                                                        <tr>

                                                            <td style="padding: 0px; margin: 0px">{{$file->file_name}}</td>
                                                            <td style="padding: 0px; margin: 0px">{{$file->user->name.' '.$file->user->surname}}</td>
                                                            <td style="padding: 0px; margin: 0px">
                                                                <a href="{{$file->file_url}}" target="_blank">
                                                                    View File
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{--VEHICLES--}}
                        <div class="tab-pane" id="link8">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">directions_car</i>
                                    </div>
                                    <h4 class="card-title">Vehicles - ({{$application->vehicles->count()}})
                                        <small class="category">All vehicles in this application</small>
                                    </h4>
                                </div>
                                <div class="card-body">

                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="vehicles-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Make</th>
                                                <th>Model</th>
                                                <th>YOM</th>
                                                <th>Reg No.</th>
                                                <th>Chassis No.</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Make</th>
                                                <th>Model</th>
                                                <th>YOM</th>
                                                <th>Reg No.</th>
                                                <th>Chassis No.</th>
                                                <th>Actions</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{--DEDUCTIONS--}}
                        <div class="tab-pane" id="link9">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">receipt</i>
                                    </div>
                                    <h4 class="card-title">Deductions
                                        <small class="category">All deductions applicable for this application</small>
                                    </h4>
                                    @if($application->status != 'ACTIVE')
                                        <button class="btn btn-primary btn-sm ml-2" data-toggle="modal" data-target="#add-deduction">
                                            Add Deduction
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="material-datatables">
                                        <table id="loans-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tbody>

                                            @if($application->loan_product_id !=null && ($application->status == 'IN REVIEW' || $application->status == 'AMENDMENT'))
                                                @foreach($feesArray as $fee)
                                                    <tr>
                                                        <td>{{$fee['name']}} </td>
                                                        <td>{{$fee['amount']}} </td>
                                                        <td>{{$fee['type']}} </td>
                                                        <td>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif

                                            @foreach($application->deductions as $deduction)
                                                <tr>
                                                    <td>{{$deduction->deduction_name}} </td>
                                                    <td>{{number_format($deduction->amount,2)}} </td>
                                                    <td>{{$deduction->type}} </td>
                                                    <td>
                                                        @if($application->status == 'IN REVIEW')
                                                            <form action="{{ url('/auto/applications/deduction/delete') }}" method="post" style="display: inline;" class="delete-deduction-form">
                                                                {{ csrf_field() }}
                                                                <input type="hidden" name="id" value="{{$deduction->id}}">
                                                                <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                                            </form>
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <th>Name</th>
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>Action</th>
                                            </tr>
                                            </tfoot>
                                        </table>
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
    <div class="modal fade" id="vehicle-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"> <span id="vehicle-modal-title">Add </span> Vehicle</h4>
                </div>
                <div class="modal-body" >
                    <form id="vehicle-form" action="{{ url('auto/applications/vehicles') }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="vehicle-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-4">
                                    <label for="logbook_file">Select Logbook</label>
                                    <input class="form-control" type="file" id="logbook_file" name="logbook_file">
                                    <a id="logbook_link" href="" target="_blank">View Uploaded</a>
                            </div>

                            <div class="col-md-4">
                                    <label for="logbook_file">Select ICF Confirmation</label>
                                    <input class="form-control" type="file" id="icf_file" name="icf_file">
                                    <a id="icf_link" href="" target="_blank">View Uploaded</a>
                            </div>

                            <div class="col-md-4">
                                    <label for="logbook_file">Select Valuation Report</label>
                                    <input type="file" class="form-control" id="valuation_file" name="valuation_file">
                                    <a id="valuation_link" href="" target="_blank">View Uploaded</a>
                            </div>
                        </div>

                        <br>


                        <div class="row">

                            <div class="col-md-12">
                                <h5 class="info-text"> Vehicle Details</h5>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <label class="control-label" for="make_id">Make</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="make_id" id="make_id" required>
                                        <option value="">--Select Make--</option>
                                        @foreach( \App\VehicleMake::all() as $make)
                                            <option value="{{ $make->id  }}">{{ $make->make }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <select class="form-control pb-0 pt-2" data-live-search="true" name="model_id" id="model_id" >
                                        <option value="">--Select Model--</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="yom">YOM</label>
                                    <input type="number" value="{{ old('yom') }}" class="form-control" id="yom" name="yom" required />
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="reg_no">Registration No.</label>
                                    <input type="text" value="{{ old('reg_no') }}" class="form-control" id="reg_no" name="reg_no" maxlength="7" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="chassis_no">Chassis Number</label>
                                    <input type="text" value="{{ old('chassis_no') }}" class="form-control" id="chassis_no" name="chassis_no" required />
                                </div>
                            </div>

                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="info-text"> Insurance Details</h5>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="insurance_company">Insurance Company</label>
                                    <input type="text" value="{{ old('insurance_company') }}" class="form-control" id="insurance_company" name="insurance_company"  />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="insurance_expiry_date">Insurance Expiry Date</label>
                                    <input type="text" name="insurance_expiry_date" id="insurance_expiry_date"   class="form-control datepicker" />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group ">
                                    <label class="control-label" for="premium_paid_by">Premium Paid By</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="premium_paid_by" id="premium_paid_by">
                                        <option value="">--Select One--</option>
                                        <option value="OWNER">OWNER</option>
                                        <option value="COMPANY">COMPANY</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="insurance_expiry_date">Premium Amount Paid</label>
                                    <input type="number" name="premium_amount_paid" id="premium_amount_paid"  class="form-control" />
                                </div>
                            </div>
                        </div>



                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="info-text"> Valuation Details</h5>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="insurance_company">Force Sale Value</label>
                                    <input type="number" value="{{ old('forced_sale_value') }}" class="form-control" id="forced_sale_value" name="forced_sale_value" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="insurance_expiry_date">Market Value</label>
                                    <input type="number" name="market_value" id="market_value"   class="form-control"/>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="insurance_expiry_date">Valuation Date</label>
                                    <input type="text" name="valuation_date" id="valuation_date"   class="form-control datepicker" />
                                </div>
                            </div>
                        </div>



                        <input type="hidden" name="id" id="id"/>
                        @if($application->status == 'IN REVIEW'
                            && $application->submitted_for_review_by != auth()->user()->id
                            && $application->submitted_for_approval_by != auth()->user()->id )
                            <div class="form-group">
                                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-window-close"></i> Close</button>
                                <button class="btn btn-success" id="save-brand"><i class="fa fa-save"></i> Save</button>
                            </div>
                        @endif

                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-individual-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"> <span id="vehicle-modal-title">Edit Individual </span> Logbook Application</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('auto/applications/update') }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-4">
                                    <label for="kra_pin_file">Select KRA PIN</label>
                                    <input class="form-control" type="file" id="kra_pin_file" name="kra_pin_file">
                                @if($application->personal_kra_pin_url != null)
                                    <a href="{{$application->personal_kra_pin_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>

                            <div class="col-md-4">
                                    <label for="id_front_file">Select ID Front</label>
                                    <input class="form-control" type="file" id="id_front_file" name="id_front_file">
                                @if($application->id_front_url != null)
                                    <a href="{{$application->id_front_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>

                            <div class="col-md-4">
                                    <label for="id_back_file">Select ID Back</label>
                                    <input type="file" class="form-control" id="id_back_file" name="id_back_file">
                                @if($application->id_back_url != null)
                                    <a href="{{$application->id_back_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>


                            <div class="col-md-4">
                                    <label for="loan_form_file">Select Loan Form</label>
                                    <input type="file" class="form-control" id="loan_form_file" name="loan_form_file">
                                @if($application->loan_form_url != null)
                                    <a href="{{$application->loan_form_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>

                            <div class="col-md-4">
                                    <label for="offer_letter_file">Select Offer Letter</label>
                                    <input type="file" class="form-control" id="offer_letter_file" name="offer_letter_file">
                                @if($application->offer_letter_url != null)
                                    <a href="{{$application->offer_letter_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>
                        </div>

                        <br>


                        <div class="row">

                            <div class="col-md-12">
                                <h5 class="info-text"> Application Details</h5>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <label class="control-label" for="payment_mode">Payment Mode</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="payment_mode" id="payment_mode" required>
                                            <option value="e-Wallet" {{$application->payment_mode == 'e-Wallet' ? 'selected' : ''}}>e-Wallet</option>
                                            <option value="M-PESA" {{$application->payment_mode == 'M-PESA' ? 'selected' : ''}}>M-PESA</option>
                                            <option value="PESALINK" {{$application->payment_mode == 'PESALINK' ? 'selected' : ''}}>PESALINK</option>
                                            <option value="EFT" {{$application->payment_mode == 'EFT' ? 'selected' : ''}}>EFT</option>
                                            <option value="RTGS" {{$application->payment_mode == 'RTGS' ? 'selected' : ''}}>RTGS</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <label class="control-label" for="source_of_business">Source of business</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="source_of_business" id="source_of_business" required>
                                        <option value="AGENT" {{$application->source_of_business == 'AGENT' ? 'selected' : ''}}>AGENT</option>
                                        <option value="WALK-IN" {{$application->source_of_business == 'WALK-IN' ? 'selected' : ''}}>WALK-IN</option>
                                        <option value="CLIENT-REFERRAL" {{$application->source_of_business == 'CLIENT-REFERRAL' ? 'selected' : ''}}>CLIENT-REFERRAL</option>
                                        <option value="APP" {{$application->source_of_business == 'APP' ? 'selected' : ''}}>APP</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="lead_originator">Lead Originator</label>
                                    <input type="text" value="{{ $application->lead_originator }}" class="form-control" id="lead_originator" name="lead_originator" />
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="requested_amount">Requested Amount</label>
                                    <input type="number" readonly value="{{ $application->requested_amount }}" class="form-control" id="requested_amount" name="requested_amount" required />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="approved_amount">Approved Amount</label>
                                    <input type="number" value="{{$application->approved_amount }}" class="form-control" id="approved_amount" name="approved_amount" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="payment_period">Payment Period (Months)</label>
                                    <input type="text" value="{{ $application->payment_period }}" class="form-control" id="payment_period" name="payment_period" required />
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="loan_product_id">Loan Product</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="loan_product_id" id="individual_loan_product" required>
                                        <option value="">--Select Loan Product--</option>
                                        @foreach( \App\LoanProduct::all() as $prod)
                                            <option value="{{ $prod->id  }}" {{$application->loan_product_id == $prod->id ? 'selected' : ''}}>{{ $prod->name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="personal_kra_pin">KRA PIN</label>
                                    <input type="text" value="{{$application->personal_kra_pin }}" class="form-control" id="personal_kra_pin" name="personal_kra_pin" required />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="loan_purpose">Loan Purpose</label>
                                    <input type="text" value="{{ $application->loan_purpose }}" class="form-control" id="loan_purpose" name="loan_purpose" required />
                                </div>
                            </div>

                        </div>

                        <input type="hidden" name="id" value="{{$application->id}}" id="id"/>
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

    <div class="modal fade" id="edit-company-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel"> <span id="vehicle-modal-title">Edit Company </span> Logbook Application</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('auto/applications/update') }}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="spoof-input" value="PUT" disabled/>
                        <div class="row">
                            <div class="col-md-6">
                                    <label for="company_kra_pin_file">Select Company KRA PIN</label>
                                    <input class="form-control" type="file" name="company_kra_pin_file">
                                @if($application->company_kra_pin_url != null)
                                    <a href="{{$application->company_kra_pin_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>

                            <div class="col-md-6">
                                    <label for="company_reg_no_file">Select Registration Certificate</label>
                                    <input class="form-control" type="file" name="company_reg_no_file">
                                @if($application->company_reg_no_url != null)
                                    <a href="{{$application->company_reg_no_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="loan_form_file">Select Loan Form</label>
                                <input type="file" class="form-control" name="loan_form_file">
                                @if($application->loan_form_url != null)
                                    <a href="{{$application->loan_form_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="offer_letter_file">Select Offer Letter</label>
                                <input type="file" class="form-control" name="offer_letter_file">
                                @if($application->offer_letter_url != null)
                                    <a href="{{$application->offer_letter_url}}" target="_blank">View Uploaded</a>
                                @else
                                    None Uploaded
                                @endif
                            </div>
                        </div>

                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="info-text"> Company Details</h5>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="company_name">Company Name</label>
                                    <input type="text" value="{{ $application->company_name }}" class="form-control" name="company_name" required />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="company_reg_no">Registration Number</label>
                                    <input type="text" value="{{ $application->company_reg_no }}" class="form-control" name="company_reg_no" required />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="company_kra_pin">Company KRA PIN</label>
                                    <input type="text" value="{{$application->company_kra_pin }}" class="form-control" name="company_kra_pin" required />
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="directors">No. Of Directors</label>
                                    <input type="number" value="{{ $application->directors }}" class="form-control" name="directors" required />
                                </div>
                            </div>

                        </div>



                        <br>
                        <div class="row">

                            <div class="col-md-12">
                                <h5 class="info-text"> Application Details</h5>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <label class="control-label" for="payment_mode">Payment Mode</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="payment_mode" required>
                                            <option value="e-Wallet" {{$application->payment_mode == 'e-Wallet' ? 'selected' : ''}}>e-Wallet</option>
                                            <option value="M-PESA" {{$application->payment_mode == 'M-PESA' ? 'selected' : ''}}>M-PESA</option>
                                            <option value="PESALINK" {{$application->payment_mode == 'PESALINK' ? 'selected' : ''}}>PESALINK</option>
                                            <option value="EFT" {{$application->payment_mode == 'EFT' ? 'selected' : ''}}>EFT</option>
                                            <option value="RTGS" {{$application->payment_mode == 'RTGS' ? 'selected' : ''}}>RTGS</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group ">
                                    <label class="control-label" for="source_of_business">Source of business</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="source_of_business" required>
                                        <option value="AGENT" {{$application->source_of_business == 'AGENT' ? 'selected' : ''}}>AGENT</option>
                                        <option value="WALK-IN" {{$application->source_of_business == 'WALK-IN' ? 'selected' : ''}}>WALK-IN</option>
                                        <option value="CLIENT-REFERRAL" {{$application->source_of_business == 'CLIENT-REFERRAL' ? 'selected' : ''}}>CLIENT-REFERRAL</option>
                                        <option value="APP" {{$application->source_of_business == 'APP' ? 'selected' : ''}}>APP</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="lead_originator">Lead Originator</label>
                                    <input type="text" value="{{ $application->lead_originator }}" class="form-control" name="lead_originator" />
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="requested_amount">Requested Amount</label>
                                    <input type="number" readonly value="{{ $application->requested_amount }}" class="form-control" name="requested_amount" required />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="approved_amount">Approved Amount</label>
                                    <input type="number" value="{{$application->approved_amount }}" class="form-control" name="approved_amount" />
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="payment_period">Payment Period (Months)</label>
                                    <input type="text" value="{{ $application->payment_period }}" class="form-control" name="payment_period" required />
                                </div>
                            </div>

                        </div>


                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="loan_product_id">Loan Product</label>
                                    <select class="selectpicker" data-style="select-with-transition" data-live-search="true" name="loan_product_id" id="company_loan_product" required>
                                        <option value="">--Select Loan Product--</option>
                                        @foreach( \App\LoanProduct::all() as $prod)
                                            <option value="{{ $prod->id  }}" {{$application->loan_product_id == $prod->id ? 'selected' : ''}}>{{ $prod->name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="loan_purpose">Loan Purpose</label>
                                    <input type="text" value="{{ $application->loan_purpose }}" class="form-control" name="loan_purpose" required />
                                </div>
                            </div>

                        </div>


                        <input type="hidden" name="id" value="{{$application->id}}" id="id"/>
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

    <div class="modal fade" id="comment-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-sm ">
           <div class="modal-content">
               <div class="modal-header">
                   <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                   <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Leave </span> a comment</h4>
               </div>
               <div class="modal-body" >
                   <form  action="{{url('auto/applications/comment')}}" method="post" enctype="multipart/form-data">
                       {{ csrf_field() }}
                       {{--spoofing--}}
                       <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>


                       <div class="row">
                           <div class="col-md-12">
                               <div class="form-group">
                                   <label class="control-label" for="reg_no">Comment</label>
                                   <input type="text" value="{{ old('comment') }}" class="form-control" id="comment" name="comment" required />
                               </div>
                           </div>
                       </div>

                       <input type="hidden" name="id" value="{{$application->id}}">


                       <div class="form-group">
                           <button class="btn btn-success btn-block" id="save-brand"><i class="material-icons">save</i> Post Comment</button>
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

    <div class="modal fade" id="file-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Upload </span> additional file</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{url('auto/applications/file')}}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>



                        <div class="row">
                            <div class="col-md-12">
                                <label for="additional_file">Select File</label>
                                <input class="form-control" type="file" id="additional_file" name="additional_file">
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="file_name">Name of File</label>
                                    <input type="text" value="{{ old('file_name') }}" class="form-control" id="file_name" name="file_name" required />
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="id" value="{{$application->id}}">


                        <div class="form-group">
                            <button class="btn btn-success btn-block" id="save-brand"><i class="material-icons">save</i> Upload</button>
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
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Reject </span> Logbook loan application</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{url('auto/applications/reject')}}" method="post" enctype="multipart/form-data">
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

                        <input type="hidden" name="id" value="{{$application->id}}">


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

    <div class="modal fade" id="approve-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Approve </span> logbook loan application</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{url('auto/applications/approve')}}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}


                        <div class="row">
                            <div class="col-md-12">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Payment Mode" tabindex="-98"
                                            name="payment_mode" id="payment_mode" required>
                                            <option value="e-Wallet" {{$application->payment_mode == 'e-Wallet' ? 'selected' : ''}}>e-Wallet</option>
                                            <option value="M-PESA" {{$application->payment_mode == 'M-PESA' ? 'selected' : ''}}>M-PESA</option>
                                            <option value="PESALINK" {{$application->payment_mode == 'PESALINK' ? 'selected' : ''}}>PESALINK</option>
                                            <option value="EFT" {{$application->payment_mode == 'EFT' ? 'selected' : ''}}>EFT</option>
                                            <option value="RTGS" {{$application->payment_mode == 'RTGS' ? 'selected' : ''}}>RTGS</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Bank Account" tabindex="-98"
                                            name="bank_id" id="bank_id">

                                        @foreach(\App\BankAccount::where('user_id',$application->user_id)->get() as $account)
                                            <option value="{{$account->id}}">{{$account->bank_name.' ('.$account->account_number.')' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="id" value="{{$application->id}}">


                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Approve and Disburse</button>
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

    <div class="modal fade" id="add-deduction" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Add </span> a deduction</h4>
                </div>
                <div class="modal-body" >
                    <form  action="{{url('auto/applications/deduction/add')}}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="deduction_name">Name of deduction</label>
                                    <input type="text" value="{{ old('deduction_name') }}" class="form-control" id="deduction_name" name="deduction_name" required />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="amount">Amount</label>
                                    <input type="number" value="{{ old('amount') }}" class="form-control" id="amount" name="amount" required />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Deduction Type" tabindex="-98"
                                            name="type" id="type" required>
                                        <option value="UPFRONT">UPFRONT</option>
                                        <option value="ADD TO PRINCIPAL">ADD TO PRINCIPAL</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="id" value="{{$application->id}}">


                        <div class="form-group">
                            <button class="btn btn-success btn-block" id="save-brand"><i class="material-icons">save</i> Add Deduction</button>
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
