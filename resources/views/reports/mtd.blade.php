@extends('layouts.app')
@section('title', 'MTD')
@push('js')
    <script>



        var repaymentsDt = $('#mtd-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax: $('#mtd-dt').data('source'), // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'year', name: 'year'},
                {data: 'month', name: 'month'},
                {data: 'loans_targeted', name: 'loans_targeted'},
                {data: 'loans_requested', name: 'loans_requested'},
                {data: 'sum_targeted', name: 'sum_targeted'},
                {data: 'sum_requested', name: 'sum_requested'},
                {data: 'target_achieved', name: 'target_achieved'},
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
                searchPlaceholder: "Search MTD",
            },
            "order": [[0, "desc"]]
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
                            <i class="material-icons">trending_up</i>
                        </div>
                        <h4 class="card-title">MTD Report. <small>{{$employer->business_name}}</small></h4>
                    </div>
                    <div class="card-body">



                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <form id="filter-form" class="form-inline form-horizontal" action="{{ '/reports/mtd' }}" method="POST">
                            @csrf

                            <div class="form-group ">
                                {{--<label class="control-label" for="user_role" style="line-height: 6px;">User Role</label>--}}

                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Choose Employer" tabindex="-98"
                                            name="employer_id" id="employer_id" required>
                                        <option value="{{ $employer->id  }}" selected>{{ $employer->business_name }}</option>
                                        @foreach( \App\Employer::all() as $emp)
                                            <option value="{{ $emp->id  }}">{{ $emp->business_name }}</option>
                                        @endforeach
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
                            <table id="mtd-dt" data-source="{{ route('mtd-dt', $employer->id) }}" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Month</th>
                                    <th>Target Loans</th>
                                    <th>Loans Issued</th>
                                    <th>Target Loans Amount</th>
                                    <th>Amount Issued</th>
                                    <th>Target Achieved</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>Year</th>
                                    <th>Month</th>
                                    <th>Target Loans</th>
                                    <th>Loans Issued</th>
                                    <th>Target Loans Amount</th>
                                    <th>Amount Issued</th>
                                    <th>Target Achieved</th>
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
