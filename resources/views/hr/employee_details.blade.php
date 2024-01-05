@extends('layouts.app')
@section('title', 'Employer Details')
@push('js')
    <script>


        $('#loans-dt').DataTable({
            processing: true, // loading icon
            serverSide: true, // this means the datatable is no longer client side
            ajax: '{{ route('employee-loans-dt', $employee->user_id) }}', // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'product', name: 'product'},
                {data: 'amount_requested', name: 'amount_requested'},
                {data: 'period_in_months', name: 'period_in_months'},
                {data: 'approval_status', name: 'approval_status'},
                {data: 'repayment_status', name: 'repayment_status'},
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


        $('.edit-employer-form').on('submit', function() {
            alert("coming soon...")
            // if (confirm('Are you sure you want to approve this loan?')) {
            //     return true;
            // }
            // return false;
        });

    </script>
@endpush

@section('content')
    <div class="container-fluid" style="margin-top: -50px">


        <div class="row">
            <div class="col-md-10 ml-auto mr-auto">
                <div class="page-categories">
                    <h3 class=" text-center">{{optional($employee->user)->surname.' '.optional($employee->user)->name}} </h3>
                    <br />
                    <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#link7" role="tablist">
                                <i class="material-icons">info</i> Employee
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#link8" role="tablist">
                                <i class="material-icons">payments</i> Loan History
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
                                    <h4 class="card-title">{{optional($employee->user)->name}} </h4>

                                </div>
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-md-4">
                                            <a href="{{$employee->passport_photo_url}}" target="_blank">
                                                <img src="{{$employee->passport_photo_url}}" width="100%" height="200dp"
                                                     style="margin-bottom: 1rem;" alt="{{optional($employee->user)->name}}">
                                            </a>


                                            <a href="{{$employee->id_url}}" class="btn btn-info btn-sm mt-2" target="_blank">
                                                View ID/passport
                                            </a>
                                        </div>

                                        <div class="col-md-4">
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Basic Salary:</td>
                                                    <td style="text-align: left"> {{number_format($employee->basic_salary)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Gross Salary:</td>
                                                    <td style="text-align: left"> {{number_format($employee->gross_salary)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Net Salary:</td>
                                                    <td style="text-align: left"> {{number_format($employee->net_salary)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Monthly Ability:</td>
                                                    <td style="text-align: left"> {{number_format($employee->max_limit)}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 150px; padding: 4px 7px;">Monthly Limit:</td>
                                                    <td style="text-align: left"> {{number_format(($employee->max_limit*100)/113.33)}}</td>
                                                </tr>


                                                </tbody>

                                            </table>



                                            <a href="{{$employee->latest_payslip_url}}" class="btn btn-info btn-sm" target="_blank">
                                                View Latest Payslip
                                            </a>

                                        </div>

                                        <div class="col-md-4">
                                            <table class="table" style="border: 1px solid #E1E1E1;">
                                                <tbody>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Payroll Number:</td>
                                                    <td style="text-align: left"> {{$employee->payroll_no}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Nature:</td>
                                                    <td style="text-align: left"> {{$employee->nature_of_work}}</td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Position:</td>
                                                    <td style="text-align: left">{{$employee->position}}</td>
                                                </tr>

{{--                                                <tr>--}}
{{--                                                    <td style="text-align: right;  background: #eee; width: 100px; padding: 4px 7px;">Status:</td>--}}
{{--                                                    <td style="text-align: left">--}}
{{--                                                        @if ($employee->status == 'PENDING')--}}
{{--                                                            <span class="badge pill badge-info">{{$employee->status}}</span>--}}
{{--                                                        @elseif ($employee->status == 'ACTIVE')--}}
{{--                                                            <span class="badge pill badge-success">{{$employee->status}}</span>--}}
{{--                                                        @elseif ($employee->status == 'REJECTED')--}}
{{--                                                            <span class="badge pill badge-danger">{{$employee->status}}</span>--}}
{{--                                                        @else--}}
{{--                                                            <span class="badge pill badge-warning">{{$employee->status}}</span>--}}
{{--                                                        @endif--}}
{{--                                                    </td>--}}
{{--                                                </tr>--}}


                                                </tbody>

                                            </table>

{{--                                            @if($employee->status == 'PENDING')--}}
{{--                                                <div class="row mt-1">--}}
{{--                                                    <div class="col-md-6">--}}
{{--                                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#approve-modal">--}}
{{--                                                            Approve--}}
{{--                                                        </button>--}}
{{--                                                    </div>--}}

{{--                                                    <div class="col-md-6">--}}

{{--                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#reject-modal">--}}
{{--                                                            Reject--}}
{{--                                                        </button>--}}
{{--                                                    </div>--}}

{{--                                                </div>--}}
{{--                                            @endif--}}

{{--                                            @if($employee->status == 'REJECTED' || $employee->status == 'INACTIVE')--}}
{{--                                                <div class="row mt-1">--}}
{{--                                                    <div class="col-md-6">--}}
{{--                                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#approve-modal">--}}
{{--                                                            Approve--}}
{{--                                                        </button>--}}
{{--                                                    </div>--}}

{{--                                                </div>--}}
{{--                                            @endif--}}

{{--                                            @if($employee->status == 'ACTIVE')--}}
{{--                                                <div class="row mt-1">--}}
{{--                                                    <div class="col-md-6">--}}
{{--                                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#update-limit-modal">--}}
{{--                                                            Update Limit--}}
{{--                                                        </button>--}}
{{--                                                    </div>--}}

{{--                                                </div>--}}
{{--                                            @endif--}}

                                        </div>

                                        <div class="col-md-12 mt-2">
                                            @if( $employee->comments != null)
                                                <strong>Comments</strong>
                                            @endif
                                            <p>{{$employee->comments}}</p>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>


                        {{--LOANS--}}
                        <div class="tab-pane" id="link8">
                            <div class="card">
                                <div class="card-header card-header-icon card-header-primary">
                                    <div class="card-icon">
                                        <i class="material-icons">payments</i>
                                    </div>
                                    <h4 class="card-title">Loans -
                                        <small class="category">Loan request history</small>
                                    </h4>
                                </div>
                                <div class="card-body">


                                    <div class="loader" style="display: none;">Loading...</div>
                                    <div class="material-datatables">
                                        <table id="loans-dt"
                                               class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Amount</th>
                                                <th>Period</th>
                                                <th>Approval</th>
                                                <th>Repayment</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Amount</th>
                                                <th>Period</th>
                                                <th>Approval</th>
                                                <th>Repayment</th>
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
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Reject </span> Employee</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('hr/employees/reject') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="reject_reason">Reason for rejecting</label>
                                    <textarea  class="form-control" name="reject_reason" required rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="employee_id" value="{{$employee->id}}" >

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
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Approve </span> new employee</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('hr/employees/approve') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="basic_salary">Basic Salary</label>
                                    <input type="number" value="{{ $employee->basic_salary }}" class="form-control pb-0 mt-2" name="basic_salary" id="basic_salary" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="gross_salary">Gross Salary</label>
                                    <input type="number" value="{{ $employee->gross_salary }}" class="form-control pb-0 mt-2" name="gross_salary" id="gross_salary" required/>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="net_salary">Net Salary</label>
                                    <input type="number" value="{{ $employee->net_salary }}" class="form-control pb-0 mt-2" name="net_salary" id="net_salary" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="max_limit">Proposed Limit</label>
                                    <input type="number" value="{{ $employee->max_limit }}" class="form-control pb-0 mt-2" name="max_limit" id="max_limit" required/>
                                </div>
                            </div>
                        </div>

{{--                        <div class="row">--}}
{{--                            <div class="col-md-12">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label class="control-label" for="comments">comments</label>--}}
{{--                                    <input type="text" class="form-control pb-0 mt-2" name="comments" id="comments" required/>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}


                        <input type="hidden" name="employee_id" value="{{$employee->id}}">


                        <input type="hidden" name="id" id="id"/>
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

    <div class="modal fade" id="update-limit-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Approve </span> new employee</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('hr/employees/update_limit') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="basic_salary">Basic Salary</label>
                                    <input type="number" value="{{ $employee->basic_salary }}" class="form-control pb-0 mt-2" name="basic_salary" id="basic_salary" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="gross_salary">Gross Salary</label>
                                    <input type="number" value="{{ $employee->gross_salary }}" class="form-control pb-0 mt-2" name="gross_salary" id="gross_salary" required/>
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="net_salary">Net Salary</label>
                                    <input type="number" value="{{ $employee->net_salary }}" class="form-control pb-0 mt-2" name="net_salary" id="net_salary" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="max_limit">Proposed Limit</label>
                                    <input type="number" value="{{ $employee->max_limit }}" class="form-control pb-0 mt-2" name="max_limit" id="max_limit" required/>
                                </div>
                            </div>
                        </div>

{{--                        <div class="row">--}}
{{--                            <div class="col-md-12">--}}
{{--                                <div class="form-group">--}}
{{--                                    <label class="control-label" for="comments">comments</label>--}}
{{--                                    <input type="text" class="form-control pb-0 mt-2" name="comments" value="{{ $employee->comments }}" id="comments" required/>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}


                        <input type="hidden" name="employee_id" value="{{$employee->id}}">


                        <input type="hidden" name="id" id="id"/>
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



@endsection
