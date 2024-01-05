@extends('layouts.app')
@section('title', 'Ageing Reports')
@push('js')
    <script>



        var repaymentsDt = $('#ageing-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax: $('#ageing-dt').data('source'), // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'loan_request_id', name: 'loan_request_id'},
                {data: 'name', name: 'name'},
                {data: 'payroll_no', name: 'payroll_no'},
                {data: 'id_no', name: 'id_no'},
                {data: 'phone_no', name: 'phone_no'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'approved_date', name: 'approved_date'},
                {data: 'opening_balance', name: 'opening_balance'},
                {data: 'installment', name: 'installment'},
                {data: 'actual_payment_done', name: 'actual_payment_done'},
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
                searchPlaceholder: "Search Loans",
            },
            "order": [[0, "desc"]]
        });


        $('#filter-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this),
                age = $('#age').val(),
                loan_product_id = $('#loan_product_id').val(),
                // employer_id = $('#employer_id').val(),
                action = form.attr('action');
                dt_url = action + '?age=' + age + '&loan_product_id=' + loan_product_id;
                // dt_url = action + '?age=' + age + '&employer_id=' + employer_id;
            console.log(action);
            console.log(dt_url);
            console.log(age);
            // console.log(employer_id);
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
                        <h4 class="card-title">Ageing Report. <small>Outstanding loan balances by employer</small></h4>
                    </div>
                    <div class="card-body">



                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <form id="filter-form" class="form-inline form-horizontal" action="{{ '/ajax/reports/ageing' }}" method="GET">
                            @csrf

{{--                            <div class="form-group ">--}}
{{--                                <div class="dropdown bootstrap-select show-tick">--}}
{{--                                    <select class="selectpicker" data-style="select-with-transition" title="Choose Employer" tabindex="-98"--}}
{{--                                            name="employer_id" id="employer_id" required>--}}
{{--                                        @foreach( \App\Employer::all() as $employer)--}}
{{--                                            <option value="{{ $employer->id  }}">{{ $employer->business_name }}</option>--}}
{{--                                        @endforeach--}}
{{--                                    </select>--}}
{{--                                </div>--}}
{{--                            </div>--}}


                            <div class="form-group ">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Loan Product" tabindex="-98"
                                            name="loan_product_id" id="loan_product_id" required>
                                            @foreach(\App\LoanProduct::all() as $lp)
                                                <option value="{{$lp->id}}">{{$lp->name}}</option>
                                            @endforeach
                                    </select>
                                </div>

                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Choose Age" tabindex="-98"
                                            name="age" id="age" required>
                                            <option value="1">Over 0 - 15 days</option>
                                            <option value="2">Over 16 - 30 days</option>
                                            <option value="3">Over 31 - 60 days</option>
                                            <option value="4">Over 61 - 90 days</option>
                                            <option value="5">Over 91 days</option>
                                    </select>
                                </div>
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
                            <table id="ageing-dt" data-source="{{ route('ageing-dt') }}" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Name</th>
                                    <th>Payroll No.</th>
                                    <th>ID No.</th>
                                    <th>Phone No.</th>
                                    <th>Loan Amount</th>
                                    <th>Date Approved</th>
                                    <th>Opening Bal.</th>
                                    <th>Installment</th>
                                    <th>Payment Received</th>
                                    <th>Installment Date</th>
                                    <th>Status</th>
                                    <th>Arrears</th>
                                    <th>Closing Bal.</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Name</th>
                                    <th>Payroll No.</th>
                                    <th>ID No.</th>
                                    <th>Phone No.</th>
                                    <th>Loan Amount</th>
                                    <th>Date Approved</th>
                                    <th>Opening Bal.</th>
                                    <th>Installment</th>
                                    <th>Payment Received</th>
                                    <th>Installment Date</th>
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
