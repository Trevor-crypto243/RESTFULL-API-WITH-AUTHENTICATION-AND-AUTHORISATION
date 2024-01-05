@extends('layouts.app')
@section('title', 'Employer Details')
@push('js')
    <script>

        const phoneInputField = document.querySelector("#phone_no");
        const phoneInput = window.intlTelInput(phoneInputField, {
            initialCountry: "ke",
            hiddenInput: "phone_no",
            utilsScript:
                "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });

        // const phoneInputField2 = document.querySelector("#manager_phone_no");
        // const phoneInput2 = window.intlTelInput(phoneInputField2, {
        //     initialCountry: "ke",
        //     hiddenInput: "manager_phone_no",
        //     utilsScript:
        //         "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        // });



        $(function() {
            // server side - lazy loading
            $('#employees-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-partner-employees', $employer->id) }}', // the route to be called via ajax
                {{--ajax: '{{ url('ajax/bms/readings/get/'. $bms->imei) }}', // the route to be called via ajax--}}
                columns: [ // datatable columns
                    {data: 'selfie', name: 'selfie'},
                    {data: 'name', name: 'name'},
                    {data: 'id_no', name: 'id_no'},
                    {data: 'payroll_no', name: 'payroll_no'},
                    {data: 'position', name: 'position'},
                    {data: 'basic_salary', name: 'basic_salary'},
                    {data: 'net_salary', name: 'net_salary'},
                    {data: 'max_limit', name: 'max_limit'},
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
                    searchPlaceholder: "Search Employees",
                },
                order: [[1, 'desc']]
            });//end datatable

            $('#employee-incomes-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-partner-employee-incomes', $employer->id) }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'payroll_no', name: 'payroll_no'},
                    {data: 'id_no', name: 'id_no'},
                    {data: 'gross_salary', name: 'gross_salary'},
                    {data: 'basic_salary', name: 'basic_salary'},
                    {data: 'net_salary', name: 'net_salary'},
                    {data: 'employment_date', name: 'employment_date'},
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
                    searchPlaceholder: "Search Incomes",
                },
                // order: [[1, 'desc']]
            });//end datatable

            $('#advance-repayments-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-advance-repayments', $employer->id) }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'payroll_no', name: 'payroll_no'},
                    {data: 'amount', name: 'amount'},
                    {data: 'employee_exists', name: 'employee_exists'},
                    {data: 'created_by', name: 'created_by'},
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
                // order: [[1, 'desc']]
            });//end datatable

            $('#hr-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-partner-employer-hr', $employer->id) }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {data: 'surname', name: 'surname'},
                    {data: 'email', name: 'email'},
                    {data: 'phone_no', name: 'phone_no'},
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
                    searchPlaceholder: "Search HR",
                },
                // order: [[1, 'desc']]
            });//end datatable

            $('#mtd-targets-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('ajax-partner-mtd-targets', $employer->id) }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'year', name: 'year'},
                    {data: 'month', name: 'month'},
                    {data: 'target_loans', name: 'target_loans'},
                    {data: 'target_loans_value', name: 'target_loans_value'},
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
                    searchPlaceholder: "Search Targets",
                },
                // order: [[1, 'desc']]
            });//end datatable

        });



        var _ModalTitle = $('#mtd-modal-title'),
            _SpoofInput = $('#mtd-spoof-input');

        //add mtd
        $(document).on('click', '.add-mtd-btn', function() {
            _ModalTitle.text('Set New');
            _SpoofInput.val('POST');
            $('#year').val('').change();
            $('#month').val('').change();
            $('#target_loans').val('');
            $('#target_loans_value').val('');
            $('#id').val('');
            $('#mtd-target-modal').modal('show');
        });
        // edit mtd
        $(document).on('click', '.edit-mtd-btn', function() {
            var _Btn = $(this);
            var _id = _Btn.attr('acs-id'),
                _Form = $('#mtd-form');

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
                        $('#year').val(data['year']).change();
                        $('#month').val(data['month']).change();
                        $('#target_loans').val(data['target_loans']);
                        $('#target_loans_value').val(data['target_loans_value']);
                        $('#mtd_id').val(data['id']);

                        // set the update url
                        var action =  _Form .attr('action');
                        // action = action + '/' + season_id;
                        console.log(action);
                        _Form .attr('action', action);

                        // open the modal
                        $('#mtd-target-modal').modal('show');
                    }
                });
            }
        });


        $('.edit-employer-form').on('submit', function() {
            alert("coming soon...")
            // if (confirm('Are you sure you want to approve this loan?')) {
            //     return true;
            // }
            // return false;
        });

        $('.enable-advance-form').on('submit', function() {
            if (confirm('Are you sure you want to ENABLE salary advance?')) {
                return true;
            }
            return false;
        });

        $('.disable-advance-form').on('submit', function() {
            if (confirm('Are you sure you want to DISABLE salary advance?')) {
                return true;
            }
            return false;
        });

        $('.delete-matrix-form').on('submit', function() {
            if (confirm('Are you sure you want to DELETE this matrix?')) {
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
                    <h3 class=" text-center">{{$employer->business_name}} </h3>
                    <br />
                    <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#link7" role="tablist">
                                <i class="material-icons">info</i> Employer
                            </a>
                        </li>
                        @if($employer->salary_advance == true)
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#link9" role="tablist">
                                    <i class="material-icons">payments</i> Payroll Data
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#link8" role="tablist">
                                    <i class="material-icons">groups</i> Employees
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#link10" role="tablist">
                                    <i class="material-icons">credit_score</i> Repayments
                                </a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link11" role="tablist">
                                <i class="material-icons">trending_up</i> MTD Targets
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
                                    <h4 class="card-title">{{$employer->business_name}} </h4>

                                </div>
                                <div class="card-body">


                                    <div class="row">
                                        <div class="col-md-4">

                                            <img src="{{$employer->business_logo_url}}" width="100%" height="200dp" alt="{{$employer->business_name}}">

                                            <p class="category mt-2">{{$employer->business_desc}}</p>
                                        </div>

                                        <div class="col-md-4">
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Reg. Number:</td>
                                                    <td style="text-align: left"> {{$employer->business_reg_no}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Address:</td>
                                                    <td style="text-align: left"> {{$employer->business_address}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">E-Mail:</td>
                                                    <td style="text-align: left">{{$employer->business_email}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Phone No.:</td>
                                                    <td style="text-align: left">{{$employer->business_phone_no}}</td>
                                                </tr>
                                                </tbody>
                                            </table>

                                            <strong class="category">Employees</strong>

                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; padding: 4px 7px;">Total Employees:</td>
                                                    <td style="text-align: left"> {{number_format(\App\Employee::where('employer_id', $employer->id)->count())}}</td>

                                                </tr>

                                                </tbody>

                                            </table>

                                        </div>

                                        <div class="col-md-4">
                                            <strong class="category">Partner Modules</strong>

                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; padding: 4px 7px;">Salary Advance:</td>

                                                    <td style="text-align: left">
                                                        @if($employer->salary_advance == true)
                                                            <span class="badge pill badge-success">ENABLED</span>
                                                        @else
                                                            <span class="badge pill badge-danger">DISABLED</span>
                                                        @endif
                                                    </td>

                                                    <td style="text-align: left">
                                                        @if($employer->salary_advance == true)
                                                            <form action="{{ url('partners/advance/disable') }}" method="post" style="display: inline;"
                                                                  class="disable-advance-form">
                                                                {{ csrf_field() }}
                                                                <input type="hidden" name="id" value="{{$employer->id}}">
                                                                <button class="btn btn-warning btn-sm">Disable</button>
                                                            </form>
                                                        @else
                                                            <form action="{{ url('partners/advance/enable') }}" method="post" style="display: inline;"
                                                                  class="enable-advance-form">
                                                                {{ csrf_field() }}
                                                                <input type="hidden" name="id" value="{{$employer->id}}">
                                                                <button class="btn btn-success btn-sm">Enable</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>

                                                </tbody>

                                            </table>

                                            <strong class="category">Advance Loan Matrix</strong>
                                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#create-matrix-modal">
                                                Add New Matrix
                                            </button>

                                            <table class="table table-no-bordered table-hover">
                                                <thead>
                                                <tr>
                                                    <td>Employment Period</td>
                                                    <td>Max Loan Period</td>
                                                    <td>Action</td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($employer->loan_period_matrices as $matrix)
                                                    <tr>
                                                        <td style="padding-top: 0px; margin-top: 0px">
                                                            {{$matrix->employment_period_from}} -  {{$matrix->employment_period_to}} Months
                                                        </td>
                                                        <td style="padding-top: 0px; margin-top: 0px">
                                                            {{$matrix->max_loan_period}} Months
                                                        </td>
                                                        <td>
                                                            <form action="{{ url('partners/advance/matrix') }}" method="post" style="display: inline;"
                                                                  class="delete-matrix-form">
                                                                {{ csrf_field() }}
                                                                {{ method_field('delete') }}

                                                                <input type="hidden" name="id" value="{{$matrix->id}}">
                                                                <button class="btn btn-danger btn-sm">Delete</button>
                                                            </form>

                                                        </td>
                                                    </tr>
                                                @endforeach

                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header card-header-icon card-header-primary">
                                            <div class="card-icon">
                                                <i class="material-icons">supervisor_account</i>
                                            </div>
                                            <h4 class="card-title">HR -
                                                <small class="category">Create HR managers</small>

                                                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#create-hr-modal">
                                                    create HR
                                                </button>
                                            </h4>

                                        </div>
                                        <div class="card-body">


                                            <div class="loader" style="display: none;">Loading...</div>
                                            <div class="material-datatables">
                                                <table id="hr-dt"
                                                       class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                                    <thead>
                                                    <tr>
                                                        <th>Id</th>
                                                        <th>Name</th>
                                                        <th>Surname</th>
                                                        <th>Email</th>
                                                        <th>Phone No.</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                    </thead>
                                                    <tfoot>
                                                    <tr>
                                                        <th>Id</th>
                                                        <th>Name</th>
                                                        <th>Surname</th>
                                                        <th>Email</th>
                                                        <th>Phone No.</th>
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


                        {{--EMPLOYEES--}}
                        <div class="tab-pane" id="link8">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">groups</i>
                                    </div>
                                    <h4 class="card-title">Employees -
                                        <small class="category">All active employees</small>
                                    </h4>
                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="employees-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Name</th>
                                                <th>ID No.</th>
                                                <th>Payroll</th>
                                                <th>Position</th>
                                                <th>Basic</th>
                                                <th>Net</th>
                                                <th>Ability</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Name</th>
                                                <th>ID No.</th>
                                                <th>Payroll</th>
                                                <th>Position</th>
                                                <th>Basic</th>
                                                <th>Net</th>
                                                <th>Ability</th>
                                                <th>Action</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
                                    </div>
                                </div>
                            </div>

                        </div>


                        {{--PAYROLL DATA--}}
                        <div class="tab-pane" id="link9">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">list</i>
                                    </div>
                                    <h4 class="card-title">Employee Payroll Data -
                                        <small class="category">Upload a list of employee Payroll Data</small>

                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#upload-modal">
                                            Upload Payroll Data
                                        </button>
                                    </h4>

                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="employee-incomes-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Payroll No.</th>
                                                <th>ID No.</th>
                                                <th>Gross</th>
                                                <th>Basic</th>
                                                <th>Net</th>
                                                <th>Employment Date</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Payroll No.</th>
                                                <th>ID No.</th>
                                                <th>Gross</th>
                                                <th>Basic</th>
                                                <th>Net</th>
                                                <th>Employment Date</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
                                    </div>
                                </div>
                            </div>

                        </div>


                        {{--REPAYMENTS--}}
                        <div class="tab-pane" id="link10">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">list</i>
                                    </div>
                                    <h4 class="card-title">Salary Advance repayments -
                                        <small class="category">Upload a list of repayments with payroll number and amount</small>

                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#upload-repayments-modal">
                                            Upload Repayments
                                        </button>
                                    </h4>

                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="advance-repayments-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Payroll No.</th>
                                                <th>Amount</th>
                                                <th>Employee exists?</th>
                                                <th>Uploaded by</th>
                                                <th>Date</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Payroll No.</th>
                                                <th>Amount</th>
                                                <th>Employee exists?</th>
                                                <th>Uploaded by</th>
                                                <th>Date</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                        <!-- end content-->
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{--MTD TARGETS--}}
                        <div class="tab-pane" id="link11">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">trending_up</i>
                                    </div>
                                    <h4 class="card-title">MTD Targets -
                                        <small class="category">Set monthly MTD targets</small>

                                        <button class="btn btn-success btn-sm add-mtd-btn">
                                            Set new Target
                                        </button>
                                    </h4>

                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="mtd-targets-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th></th>
                                                <th>Year</th>
                                                <th>Month</th>
                                                <th>Target Loans</th>
                                                <th>Target Loans Value</th>
                                                <th>Action</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Year</th>
                                                <th>Month</th>
                                                <th>Target Loans</th>
                                                <th>Target Loans Value</th>
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





    {{--modals--}}
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


    <div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="product-modal-title">Upload </span> Payroll Data</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('partners/employees/incomes/upload') }}" method="post" id="product-form"  enctype="multipart/form-data">
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
                                        <input type="file" required name="file" />
                                    </span>
                                    <br />
                                    <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                </div>

                                <a href="{{url('samples/employee_incomes.csv')}}">Download sample</a>

                            </div>
                        </div>


                        <input type="hidden" name="employer_id" value="{{$employer->id}}">

                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Upload and Save</button>
                        </div>
                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>


    <div class="modal fade" id="upload-repayments-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="product-modal-title">Upload </span> Repayments</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('partners/employees/repayments/upload') }}" method="post" id="product-form"  enctype="multipart/form-data">
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
                                        <input type="file" required name="file" />
                                    </span>
                                    <br />
                                    <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                </div>

                                <a href="{{url('samples/inua_repayments.csv')}}">Download sample</a>

                            </div>
                        </div>


                        <input type="hidden" name="employer_id" value="{{$employer->id}}">

                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Upload and Save</button>
                        </div>
                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>


    <div class="modal fade" id="create-hr-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Add </span> New HR manager</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('partners/employers/hr/create') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="name">Name</label>
                                    <input type="text" value="{{ old('name') }}" class="form-control" id="name" name="name" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="surname">Surname</label>
                                    <input type="text" value="{{ old('surname') }}" class="form-control" id="surname" name="surname" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="email">Email</label>
                                    <input type="email" value="{{ old('email') }}" class="form-control pb-0 mt-2" name="email" id="email" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="id_no">ID. NO</label>
                                    <input type="text" value="{{ old('id_no') }}" class="form-control pb-0 mt-2" name="id_no" id="id_no" required/>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="phone_no">Phone No.</label>
                                    <input id="phone_no" type="tel"  class="form-control pb-0 mt-2"  name="" required/>
                                </div>
                            </div>
                        </div>


                        <input type="hidden" name="employer_id" value="{{$employer->id}}">


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



    <div class="modal fade" id="create-matrix-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Add </span> New Inua Loan Period Matrix</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('partners/advance/matrix') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <h6>Employment period (Months)</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="employment_period_from">From</label>
                                    <input type="number" value="{{ old('employment_period_from') }}" class="form-control" id="employment_period_from" name="employment_period_from" required />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="employment_period_to">To</label>
                                    <input type="number" value="{{ old('employment_period_to') }}" class="form-control" id="employment_period_to" name="employment_period_to" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="max_loan_period">Max. Loan Period (Months)</label>
                                    <input type="number" value="{{ old('max_loan_period') }}" class="form-control pb-0 mt-2" name="max_loan_period" id="max_loan_period" required/>
                                </div>
                            </div>

                        </div>

                        <input type="hidden" name="employer_id" value="{{$employer->id}}">


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


    <div class="modal fade" id="mtd-target-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="mtd-modal-title">Set New </span> MTD Target</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('partners/mtd') }}" method="post" id="mtd-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="mtd-spoof-input" value="PUT" disabled/>

                        <h6>Target Period</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Year" tabindex="-98"
                                            name="year" id="year" required>
                                            <option value="2023">2023</option>
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-6">
                                <select class="selectpicker" data-style="select-with-transition" title="Select Month" tabindex="-98"
                                        name="month" id="month" required>
                                    <option value="1">JANUARY</option>
                                    <option value="2">FEBRUARY</option>
                                    <option value="3">MARCH</option>
                                    <option value="4">APRIL</option>
                                    <option value="5">MAY</option>
                                    <option value="6">JUNE</option>
                                    <option value="7">JULY</option>
                                    <option value="8">AUGUST</option>
                                    <option value="9">SEPTEMBER</option>
                                    <option value="10">OCTOBER</option>
                                    <option value="11">NOVEMBER</option>
                                    <option value="12">DECEMBER</option>
                                </select>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="target_loans">Target Loans</label>
                                    <input type="number" value="{{ old('target_loans') }}" class="form-control pb-0 mt-2" name="target_loans" id="target_loans" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="target_loans_value">Target Loans Value</label>
                                    <input type="number" value="{{ old('target_loans_value') }}" class="form-control pb-0 mt-2" name="target_loans_value" id="target_loans_value" required/>
                                </div>
                            </div>

                        </div>

                        <input type="hidden" name="employer_id" value="{{$employer->id}}">


                        <input type="hidden" name="mtd_id" id="mtd_id"/>
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
