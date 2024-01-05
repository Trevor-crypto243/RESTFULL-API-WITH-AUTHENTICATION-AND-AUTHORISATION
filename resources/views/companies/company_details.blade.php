@extends('layouts.app')
@section('title', 'Company Details')
@push('js')
    <script>

        $(function() {
            // server side - lazy loading
            $('#invoices-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-company-idfs', $company->id) }}', // the route to be called via ajax
                {{--ajax: '{{ url('ajax/bms/readings/get/'. $bms->imei) }}', // the route to be called via ajax--}}
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'total_invoice_amount', name: 'total_invoice_amount'},
                    {data: 'approved_amount', name: 'approved_amount'},
                    {data: 'agent_code', name: 'agent_code'},
                    {data: 'offer_status', name: 'offer_status'},
                    {data: 'payment_status', name: 'payment_status'},
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
                    searchPlaceholder: "Search IDFs",
                },
                order: [[1, 'desc']]
            });//end datatable


            $('#approved-invoices-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-approved-company-invoices', $company->id) }}', // the route to be called via ajax
                {{--ajax: '{{ url('ajax/bms/readings/get/'. $bms->imei) }}', // the route to be called via ajax--}}
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'invoice_number', name: 'invoice_number'},
                    {data: 'grn_no', name: 'grn_no'},
                    {data: 'lpo_no', name: 'lpo_no'},
                    {data: 'invoice_amount', name: 'invoice_amount'},
                    {data: 'invoice_date', name: 'invoice_date'},
                    {data: 'expected_payment_date', name: 'expected_payment_date'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'approval_status', name: 'approval_status'},
                    {data: 'payment_status', name: 'payment_status'},
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
                    searchPlaceholder: "Search Invoices",
                },
                order: [[1, 'desc']]
            });//end datatable


            $('#company-loans-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-company-idf-loans', $company->id) }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'product', name: 'product'},
                    {data: 'amount_requested', name: 'amount_requested'},
                    {data: 'period_in_months', name: 'period_in_months'},
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


        });


    </script>
@endpush

@section('content')
    <div class="container-fluid" style="margin-top: -50px">


        <div class="row">
            <div class="col-md-10 ml-auto mr-auto">
                <div class="page-categories">
                    <h3 class=" text-center">Company Details</h3>
                    <br />
                    <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#link7" role="tablist">
                                <i class="material-icons">info</i> Company
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link8" role="tablist">
                                <i class="material-icons">receipt</i> IDF applications
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link9" role="tablist">
                                <i class="material-icons">done_all</i> Approved Invoices
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link10" role="tablist">
                                <i class="material-icons">local_atm</i> Company Loans
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
                                                {{$company->business_name}} - {{$company->type}}
                                            </h4>
                                        </div>
                                        <div class="col-md-4">
                                            <h4 class="card-title">
                                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#upload-docs-modal">
                                                    Update Documents
                                                </button>

                                                <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#upload-files-modal">
                                                    Upload Additional File
                                                </button>

                                            </h4>
                                        </div>
                                    </div>
                                </div>


                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong class="category">Company details</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <tbody>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Type</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{$company->type}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Status</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        @if ($company->status == 'ACTIVE')
                                                            <span class="badge pill badge-success">{{$company->status}}</span>
                                                        @elseif ($company->status == 'PENDING')
                                                            <span class="badge pill badge-info">{{$company->status}}</span>
                                                        @elseif ($company->status == 'REJECTED')
                                                            <span class="badge pill badge-danger">{{$company->status}}</span>
                                                        @elseif ($company->status == 'INCOMPLETE')
                                                            <span class="badge pill badge-warning">{{$company->status}}</span>
                                                        @endif

                                                    </td>
                                                </tr>



                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">KRA PIN</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        <a href="{{ $company->tax_pin_url }}" class="btn btn-primary btn-link btn-sm" target="_blank"> {{$company->tax_pin}} </a>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Irrevocable Letter</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        @if($company->irrevocable_letter == null)
                                                            <a href="#" class="btn btn-primary btn-link btn-sm" >  Not Available </a>
                                                        @else
                                                            <a href="{{ $company->irrevocable_letter }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View </a>
                                                        @endif
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Reg. Number</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        <a href="{{ $company->registration_certificate_url }}" class="btn btn-primary btn-link btn-sm" target="_blank">
                                                            {{$company->reg_no}}
                                                        </a>
                                                    </td>
                                                </tr>

                                                @if($company->type == 'LIMITED COMPANY')
                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">Articles of Assoc.</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ $company->articles_url }}" class="btn btn-primary btn-link btn-sm" target="_blank">
                                                                View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">CR12.</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ $company->cr12_url }}" class="btn btn-primary btn-link btn-sm" target="_blank">
                                                                View
                                                            </a>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">Board Resolution</td>
                                                        <td style="padding: 0px; margin: 0px">
                                                            @if($company->board_resolution == null)
                                                                <a href="#" class="btn btn-primary btn-link btn-sm" >  Not Available </a>
                                                            @else
                                                                <a href="{{ $company->board_resolution }}" class="btn btn-primary btn-link btn-sm" target="_blank"> View </a>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                @endif


                                                <tr>
                                                    <td style="padding: 0px; margin: 0px">Wallet balance:</td>
                                                    <td style="padding: 0px; margin: 0px">
                                                        {{optional($company->wallet)->currency.' '.number_format(optional($company->wallet)->current_balance)}}
                                                        <br>
                                                        <a style="padding-left: 0px" href="{{url('wallet/company/'.$company->wallet_id)}}"
                                                           class="btn btn-primary btn-link btn-sm">
                                                            <i class="material-icons">account_balance_wallet</i> View Wallet
                                                        </a>

                                                    </td>
                                                </tr>


                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-6">
                                            <strong class="category">Owner details</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <tbody>

                                                <tr>
                                                    <td>Created By</td>
                                                    <td>
                                                        {{$company->owner->name}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Phone No.</td>
                                                    <td>
                                                        {{$company->owner->phone_no}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>E-Mail</td>
                                                    <td>
                                                        {{$company->owner->email}}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td >ID No.</td>
                                                    <td >
                                                        {{$company->owner->id_no}}
                                                    </td>
                                                </tr>


                                                </tbody>
                                            </table>


                                            <strong class="category">Additional Files</strong>
                                            <table class="table table-no-bordered table-hover">
                                                <thead>
                                                <tr>
                                                    <td>Filename</td>
                                                    <td>Uploaded By</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($company->additional_files as $file)
                                                    <tr>
                                                        <td style="padding: 0px; margin: 0px">
                                                            <a href="{{ $file->file_link }}" class="btn btn-primary btn-link btn-sm" target="_blank"> {{$file->file_name}} </a>
                                                        </td>
                                                        <td style="padding: 0px; margin: 0px">{{optional($file->creator)->name}}</td>

                                                    </tr>
                                                @endforeach

                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-12">
                                            <strong class="category">Directors:</strong>

                                            <table class="table table-no-bordered table-hover">
                                                <thead>
                                                <tr>
                                                    <td></td>
                                                    <td><strong>NAME</strong></td>
                                                    <td><strong>Phone No.</strong></td>
                                                    <td><strong>ID. NO</strong></td>
                                                    <td><strong>ID. PHOTO</strong></td>
                                                    <td><strong>KRA PIN</strong></td>
                                                </tr>
                                                </thead>
                                                <tbody>

                                                @foreach(\App\CompanyDirector::where('company_id', $company->id)->get() as $director)
                                                    <tr>
                                                        <td style=" ">
                                                            <img src="{{$director->passport_photo_url}}" width="100dp" height="100dp" alt="{{$director->name}}">
                                                        </td>
                                                        <td style="padding: 0px; margin: 0px">{{$director->name}}</td>
                                                        <td style="padding: 0px; margin: 0px">{{$director->phone_no}}</td>
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
                                    <h4 class="card-title">Invoices discount applications

                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#upload-invoices-modal">
                                            Upload Paid Invoices
                                        </button>
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
                                                <th>Invoice Amount</th>
                                                <th>Approved Amount</th>
                                                <th>Agent Code</th>
                                                <th>Offer</th>
                                                <th>Payment</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Invoice Amount</th>
                                                <th>Approved Amount</th>
                                                <th>Agent Code</th>
                                                <th>Offer</th>
                                                <th>Payment</th>
                                                <th>Action</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{--APPROVED INVOICES--}}
                        <div class="tab-pane" id="link9">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">done_all</i>
                                    </div>
                                    <h4 class="card-title">Approved Invoices

{{--                                        @if($invoiceDiscount->invoices->count() > 0)--}}
{{--                                            <a href="{{url('invoices/list',$invoiceDiscount->id)}}" class="btn btn-success btn-sm ml-3">--}}
{{--                                                Download invoice list--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
                                    </h4>
                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="approved-invoices-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Invoice No.</th>
                                                <th>GRN No.</th>
                                                <th>LPO No.</th>
                                                <th>Amount</th>
                                                <th>Invoice Date</th>
                                                <th>Expected Payment Date</th>
                                                <th>Date Created</th>
                                                <th>Approval</th>
                                                <th>Payment</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Invoice No.</th>
                                                <th>GRN No.</th>
                                                <th>LPO No.</th>
                                                <th>Amount</th>
                                                <th>Invoice Date</th>
                                                <th>Expected Payment Date</th>
                                                <th>Date Created</th>
                                                <th>Approval</th>
                                                <th>Payment</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{--COMPANY LOANS --}}
                        <div class="tab-pane" id="link10">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">local_atm</i>
                                    </div>
                                    <h4 class="card-title">Company Loans

{{--                                        @if($invoiceDiscount->invoices->count() > 0)--}}
{{--                                            <a href="{{url('invoices/list',$invoiceDiscount->id)}}" class="btn btn-success btn-sm ml-3">--}}
{{--                                                Download invoice list--}}
{{--                                            </a>--}}
{{--                                        @endif--}}
                                    </h4>
                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="company-loans-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Product</th>
                                                <th>Amount</th>
                                                <th>Period</th>
                                                <th>Approval</th>
                                                <th>Repayment</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>ID</th>
                                                <th>Product</th>
                                                <th>Amount</th>
                                                <th>Period</th>
                                                <th>Approval</th>
                                                <th>Repayment</th>
                                                <th>Actions</th>
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
    <div class="modal fade" id="upload-docs-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="product-modal-title">Upload </span> Company Documents</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('company/documents/upload') }}" method="post" id="product-form"  enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="product-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="kra_pin_file">Select KRA PIN</label>
                                <input type="file" class="form-control" name="kra_pin_file" id="kra_pin_file" />

                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="irrevocable_letter_file">Select Irrevocable Letter</label>
                                <input type="file" class="form-control" name="irrevocable_letter_file" id="irrevocable_letter_file" />
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="registration_certificate_file">Registration Certificate</label>
                                <input type="file" class="form-control" name="registration_certificate_file" id="registration_certificate_file" />

                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="articles_file">Articles of Association</label>
                                <input type="file" class="form-control" name="articles_file" id="articles_file" />
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label" for="cr12_file">CR12</label>
                                <input type="file" class="form-control" name="cr12_file" id="cr12_file" />

                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="board_resolution_file">Board Resolution</label>
                                <input type="file" class="form-control" name="board_resolution_file" id="board_resolution_file" />
                            </div>

                        </div>


                        <input type="hidden" name="company_id" value="{{$company->id}}">

                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Upload</button>
                        </div>
                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="upload-files-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="file-modal-title">Upload </span> Additional Company files</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('company/documents/additional/upload') }}" method="post" id="product-form"  enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="product-spoof-input" value="PUT" disabled/>

                        <input type="hidden" name="company_id" value="{{$company->id}}">

                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label" for="file_name">File Name</label>
                                <input type="text" class="form-control" name="file_name" id="file_name" />
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label" for="uploaded_file">Select File</label>
                                <input type="file" class="form-control" name="uploaded_file" id="uploaded_file" />
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Upload</button>
                        </div>
                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>

    <div class="modal fade" id="upload-invoices-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="file-modal-title">Upload </span> Company's FULLY paid invoices</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('company/invoices/paid/upload') }}" method="post"   enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="product-spoof-input" value="PUT" disabled/>

                        <input type="hidden" name="company_id" value="{{$company->id}}">

                        <div class="row">
                            <div class="col-md-12">
                                <label class="form-label" for="uploaded_file">Select File</label>
                                <input type="file" class="form-control" name="uploaded_file" id="uploaded_file" />
                            </div>

                            <div class="col-md-12 mt-2">
                                <a href="{{url('samples/paid_invoices.csv')}}">Download sample</a>
                            </div>
                        </div>



                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Upload</button>
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
