@extends('layouts.app')
@section('title', 'Inua Repayments Due')
@push('js')
    <script>



        var repaymentsDt = $('#repayments-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax: $('#repayments-dt').data('source'), // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'payroll_no', name: 'payroll_no'},
                {data: 'branch', name: 'branch'},
                {data: 'id_no', name: 'id_no'},
                {data: 'phone_no', name: 'phone_no'},
                {data: 'previous_loans', name: 'previous_loans'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'loan_term', name: 'loan_term'},
                {data: 'approved_date', name: 'approved_date'},
                {data: 'opening_balance', name: 'opening_balance'},
                {data: 'installment', name: 'installment'},
                {data: 'date_paid', name: 'date_paid'},
                {data: 'status', name: 'status'},
                {data: 'arrears', name: 'arrears'},
                {data: 'closing_loan_balance', name: 'closing_loan_balance'},
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
                searchPlaceholder: "Search Repayments",
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
                employer_id = $('#employer_id').val(),
                action = form.attr('action');
                dt_url = action + '?date_range=' + date_range + '&employer_id=' + employer_id;
            console.log(action);
            console.log(dt_url);
            console.log(date_range);
            console.log(employer_id);
            repaymentsDt.ajax.url(dt_url).load();

        });




    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">phone_android</i>
                        </div>
                        <h4 class="card-title">Repayments Due. <small>Repayments due by employer</small></h4>
                    </div>
                    <div class="card-body">



                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <form id="filter-form" class="form-inline form-horizontal" action="{{ '/ajax/reports/inua_repayments' }}" method="GET">
                            @csrf

                            <div class="form-group ">
                                {{--<label class="control-label" for="user_role" style="line-height: 6px;">User Role</label>--}}

                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Choose Employer" tabindex="-98"
                                            name="employer_id" id="employer_id" required>
                                        @foreach( \App\Employer::all() as $employer)
                                            <option value="{{ $employer->id  }}">{{ $employer->business_name }}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>

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
                            <table id="repayments-dt" data-source="{{ route('repayments-dt') }}" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Payroll No.</th>
                                    <th>Branch</th>
                                    <th>ID No.</th>
                                    <th>Phone No.</th>
                                    <th>Previous Loans</th>
                                    <th>Loan Amount</th>
                                    <th>Loan Term</th>
                                    <th>Approved On</th>
                                    <th>Opening Bal.</th>
                                    <th>Installment</th>
                                    <th>DueDate</th>
                                    <th>Status</th>
                                    <th>Arrears</th>
                                    <th>Closing Bal.</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Payroll No.</th>
                                    <th>Branch</th>
                                    <th>ID No.</th>
                                    <th>Phone No.</th>
                                    <th>Previous Loans</th>
                                    <th>Loan Amount</th>
                                    <th>Loan Term</th>
                                    <th>Approved On</th>
                                    <th>Opening Bal.</th>
                                    <th>Installment</th>
                                    <th>DueDate</th>
                                    <th>Status</th>
                                    <th>Arrears</th>
                                    <th>Closing Bal.</th>
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







@endsection
