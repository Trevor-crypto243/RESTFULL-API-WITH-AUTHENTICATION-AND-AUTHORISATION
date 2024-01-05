@extends('layouts.app')
@section('title', 'Running Loan Balance')
@push('js')
    <script>



        var reportsDt = $('#reports-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax: $('#reports-dt').data('source'), // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'outstanding_due', name: 'outstanding_due'},
                {data: 'name', name: 'name'},
                {data: 'payroll_no', name: 'payroll_no'},
                {data: 'id_no', name: 'id_no'},
                {data: 'loan_amount', name: 'loan_amount'},
                {data: 'created_at', name: 'created_at'},
                {data: 'loan_period', name: 'loan_period'},
                {data: 'installment', name: 'installment'},
                {data: 'running_balance', name: 'running_balance'},

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
                searchPlaceholder: "Search Running Loan Balance",
            },
            "order": [[0, "desc"]]
        });



        $('#filter-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this),
                loan_product_id = $('#loan_product_id').val(),
                //date_approved = $('#date-approved').val(),
                action = form.attr('action');
                dt_url = action + '?loan_product_id=' + loan_product_id;
            console.log(action);
            console.log(dt_url);
            console.log(loan_product_id);
            reportsDt.ajax.url(dt_url).load();

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
                        <h4 class="card-title">Running Loan Balance. <small>Outstanding principal plus interest due</small></h4>
                    </div>
                    <div class="card-body">



                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <form id="filter-form" class="form-inline form-horizontal" action="{{ '/ajax/reports/running_lb' }}" method="GET">
                            @csrf

                            <div class="form-group ">
                                {{--<label class="control-label" for="user_role" style="line-height: 6px;">User Role</label>--}}

                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Choose Loan Product" tabindex="-98"
                                            name="loan_product_id" id="loan_product_id" required>
                                        @foreach( \App\LoanProduct::all() as $lp)
                                            <option value="{{ $lp->id  }}">{{ $lp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>


{{--                                <div class="form-group text-left mb-2 mx-sm-3">--}}
{{--                                    <label class="control-label" for="date-approved">Date Approved</label>--}}
{{--                                    <input type="text" name="date_approved" id="date-approved" class="form-control datepicker" required/>--}}
{{--                                </div>--}}



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
                            <table id="reports-dt" data-source="{{ route('running-lb-dt') }}" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>Outstanding Bal.</th>
                                    <th>Name</th>
                                    <th>Payroll No.</th>
                                    <th>ID No.</th>
                                    <th>Principal</th>
                                    <th>Date Disbursed</th>
                                    <th>Loan Term</th>
                                    <th>Installment</th>
                                    <th>Running Bal.</th>

                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>Outstanding Bal.</th>
                                    <th>Name</th>
                                    <th>Payroll No.</th>
                                    <th>ID No.</th>
                                    <th>Principal</th>
                                    <th>Date Disbursed</th>
                                    <th>Loan Term</th>
                                    <th>Installment</th>
                                    <th>Running Bal.</th>
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
