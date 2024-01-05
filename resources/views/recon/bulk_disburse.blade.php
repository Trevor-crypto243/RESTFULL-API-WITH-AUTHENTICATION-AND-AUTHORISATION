@extends('layouts.app')
@section('title', 'Bulk Disburse')
@push('js')
    <script>

        const phoneInputField = document.querySelector("#phone_no");
        const phoneInput = window.intlTelInput(phoneInputField, {
            initialCountry: "ke",
            onlyCountries: ["ke"],
            hiddenInput: "phone_no",
            utilsScript:
                "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });

        var disburseDt = $('#disburse-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax: $('#disburse-dt').data('source'), // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'created_at', name: 'created_at'},
                {data: 'created_by', name: 'created_by'},
                {data: 'msisdn', name: 'msisdn'},
                {data: 'name', name: 'name'},
                {data: 'amount', name: 'amount'},
                {data: 'receipt', name: 'receipt'},
                {data: 'status', name: 'status'},
                {data: 'description', name: 'description'},
                {data: 'narration', name: 'narration'},
            ],
            dom: 'Blfrtip',
            buttons: [
                //'copy', 'excel', 'pdf',
                { "extend": 'copy', "text":'Copy Data',"className": 'btn btn-info btn-xs' },
                { "extend": 'excel', "text":'Export To Excel',"className": 'btn btn-sm btn-success' },
                { "extend": 'pdf', "text":'Export To PDF',"className": 'btn btn-default btn-xs' }
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
                searchPlaceholder: "Search Disbursements",
            },
            "order": [[0, "desc"]]
        });



        // initialize date range picker
        $('#date-range').daterangepicker({
            opens: 'center',
            locale: {
                format: 'YYYY/MM/DD'
            }
        }, function(start, end, label) {
            //console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
        });


        $('#filter-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this),
                date_range = $('#date-range').val(),
                action = form.attr('action');
                dt_url = action + '?date_range=' + date_range;
            // console.log(action);
            // console.log(date_range);
            disburseDt.ajax.url(dt_url).load();

        });




    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="total_customers" style="padding-bottom: 20px">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">calendar_today</i>
                        </div>
                        <p class="card-category">Disbursed Today</p>
                        <h4 class="card-title">KES {{number_format($amountToday)}}</h4>
                    </div>

                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="checkoff_customers" style="padding-bottom: 20px">
                    <div class="card-header card-header-icon card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">group_add</i>
                        </div>
                        <p class="card-category">Recipients Today</p>
                        <h4 class="card-title"> {{number_format($recipientsToday)}}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="approved_today" style="padding-bottom: 20px">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">functions</i>
                        </div>
                        <p class="card-category">Total Disbursed</p>
                        <h4 class="card-title">KES {{number_format($amountTotal)}}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="paid_today" style="padding-bottom: 20px">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="fa fa-users"></i>
                        </div>
                        <p class="card-category">Total Recipients</p>
                        <h4 class="card-title" id="paidToday"> {{number_format($recipientsTotal)}}</h4>
                    </div>

                </div>
            </div>


            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">phone_android</i>
                        </div>
                        <h4 class="card-title">Disburse. <small>Manually disburse to M-Pesa numbers</small></h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#single-modal">
                                    <i class="material-icons">person_outline</i>Send to one
                                </button>

                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#upload-modal">
                                    <i class="material-icons">file_upload</i> Send to many (Upload List)
                                </button>
                        </div>

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <form id="filter-form" class="form-inline form-horizontal" action="{{ '/ajax/recon/bulk_disbursements' }}" method="GET">
                            @csrf
                            <div class="form-group text-left mb-2 mx-sm-3">
                                <label class="control-label" for="date-range">Date Range</label>
                                <input type="text" name="date_range" id="date-range" class="form-control"/>
                            </div>
                            <div class="form-group mb-2">
                                <button type="submit" class="btn btn-success btn-sm"> Filter</button>
                            </div>
                        </form>

                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="disburse-dt" data-source="{{ route('disburse-dt') }}" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Created By</th>
                                    <th>Phone No.</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>TRX. Code</th>
                                    <th>Status</th>
                                    <th>Desc.</th>
                                    <th>Narration</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Created By</th>
                                    <th>Phone No.</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>TRX. Code</th>
                                    <th>Status</th>
                                    <th>Desc.</th>
                                    <th>Narration</th>
                                </tr>
                                </tfoot>
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
    <div class="modal fade" id="single-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Send to one </span> M-Pesa account</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('recon/bulk_disburse/single') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="phone_no">Phone No.</label>
                                    <input id="phone_no" type="tel"  class="form-control pb-0 mt-2"  name="" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="amount">Amount</label>
                                    <input type="number" value="{{ old('amount') }}" class="form-control pb-0 mt-2" name="amount" id="amount" required/>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="narration">Narration</label>
                                    <input type="text" value="{{ old('narration') }}" class="form-control pb-0 mt-2" name="narration" id="narration" required/>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Send</button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="product-modal-title">Upload </span> Recipients</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('recon/bulk_disburse/upload') }}" method="post" id="product-form"  enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="product-spoof-input" value="PUT" disabled/>



                        <div class="col-md-4 text-center">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                                {{--<div class="fileinput-new thumbnail img-responsive">--}}
                                {{--<img src="" alt="Product Image">--}}
                                {{--</div>--}}
                                <div class="fileinput-preview fileinput-exists thumbnail img-responsive"></div>
                                <div>
                                    <span class="btn btn-round btn-rose btn-file">
                                        <span class="fileinput-new">Select File</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="file" name="file" />
                                    </span>
                                    <br />
                                    <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                </div>

                                <a href="{{url('samples/mpesa_bulk_disbursements.csv')}}">Download sample</a>

                            </div>
                        </div>



                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Upload and Send</button>
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
