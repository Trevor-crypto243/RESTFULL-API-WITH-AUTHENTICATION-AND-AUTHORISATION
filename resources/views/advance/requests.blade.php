@extends('layouts.app')
@section('title', $request)
@push('js')
    <script>

        var disburseDt = $('#requests-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            {{--ajax: '{{ route('advance-requests-dt',[$employer_id,$type]) }}', // the route to be called via ajax--}}
            ajax:{
                url:"{{route('advance-requests-dt')}}",
                type: "POST",
                data: function (d) {
                    d._token = "{{ csrf_token() }}";
                    d.employee_id = "{{ $employer_id }}";
                    d.type = "{{ $type }}";
                },

            },
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'surname', name: 'surname'},
                {data: 'employer', name: 'employer'},
                {data: 'payroll_no', name: 'payroll_no'},
                {data: 'loan_product', name: 'loan_product'},
                {data: 'amount_requested', name: 'amount_requested'},
                {data: 'period_in_months', name: 'period_in_months'},
                {data: 'monthly_installment', name: 'monthly_installment'},
                {data: 'quicksava_status', name: 'quicksava_status'},
                {data: 'hr_status', name: 'hr_status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'actions', name: 'actions'},
            ],
            dom: 'Blfrtip',
            buttons: [
                //'copy', 'excel', 'pdf',
                // { "extend": 'copy', "text":'Copy Data',"className": 'btn btn-info btn-xs' },
                { "extend": 'excel', "text":'Export To Excel',"className": 'btn btn-sm btn-success' },
                // { "extend": 'pdf', "text":'Export To PDF',"className": 'btn btn-default btn-xs' }
            ],
            columnDefs: [
                {searchable: true, targets: [11]},
                // {orderable: false, targets: [5]}
            ],
            "pagingType": "full_numbers",
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Requests",
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
                            <i class="material-icons">analytics</i>
                        </div>
                        <h4 class="card-title">{{$request}}. <small> Advance loan product</small></h4>
                    </div>
                    <div class="card-body">

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <form id="filter-form" class="form-inline form-horizontal" action="" method="POST">
                            @csrf
                            <div class="form-group text-left mb-2 mx-sm-3">
                                <input type="hidden" value="{{$type}}" name="type" >
                                <select class="selectpicker" data-style="select-with-transition" title="Choose Employer" tabindex="-98"
                                        name="employer_id" id="employer_id" required>
                                    @if($employer_id != 0)
                                        <option selected value="{{ optional(\App\Employer::find($employer_id))->id  }}">{{ optional(\App\Employer::find($employer_id))->business_name }}</option>
                                    @endif
                                    @foreach( \App\Employer::all() as $employer)
                                        <option value="{{ $employer->id  }}">{{ $employer->business_name }}</option>
                                    @endforeach
                                </select>
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
                            <table id="requests-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Surname</th>
                                    <th>Employer</th>
                                    <th>Payroll No.</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Installment</th>
                                    <th>Status</th>
                                    <th>HR Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Surname</th>
                                    <th>Employer</th>
                                    <th>Payroll No.</th>
                                    <th>Product</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Installment</th>
                                    <th>Status</th>
                                    <th>HR Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
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
