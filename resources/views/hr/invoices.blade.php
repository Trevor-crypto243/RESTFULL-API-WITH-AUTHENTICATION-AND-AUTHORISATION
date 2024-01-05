@extends('layouts.app')
@section('title', $title)
@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#employees-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route($ajax) }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'company', name: 'company'},
                    {data: 'invoices', name: 'invoices'},
                    {data: 'total_amount', name: 'total_amount'},
                    {data: 'approved_amount', name: 'approved_amount'},
                    {data: 'offer_status', name: 'offer_status'},
                    {data: 'payment_status', name: 'payment_status'},
                    {data: 'actions', name: 'actions'}
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
                    searchPlaceholder: "Search Invoices",
                },
                "order": [[0, "desc"]]
            });

            // live search





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
                            <i class="material-icons">list</i>
                        </div>
                        <h4 class="card-title">{{$title}}</h4>
                    </div>
                    <div class="card-body">

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="employees-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>Company</th>
                                    <th>Invoices</th>
                                    <th>Total Amount</th>
                                    <th>Approved Amount</th>
                                    <th>Offer</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th></th>
                                    <th>Company</th>
                                    <th>Invoices</th>
                                    <th>Total Amount</th>
                                    <th>Approved Amount</th>
                                    <th>Offer</th>
                                    <th>Payment</th>
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
