@extends('layouts.app')
@section('title', 'B2C Reconciliations')
@push('js')
    <script>
        $(function() {
            // server side - lazy loading
            $('#loans-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('b2c-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'amount', name: 'amount'},
                    {data: 'msisdn', name: 'msisdn'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'wallet', name: 'wallet'},
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
                    searchPlaceholder: "Search B2C",
                },
                "order": [[0, "desc"]]
            });

            // live search


            $(document).on('click', '.recon-b2c-btn', function() {
                var _Btn = $(this);
                var _id = _Btn.attr('acs-id'),
                    _msisdn = _Btn.attr('acs-msisdn'),
                    _Form = $('#recon-form');

                $('#id').val(_id);
                $('#msisdn').val(_msisdn);

                // open the modal
                $('#recon-modal').modal('show');

            });





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
                        <h4 class="card-title">B2C Reconciliations. <small>Reconcile transactions that haven't received a callback</small></h4>
                    </div>
                    <div class="card-body">

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')
                        @include('layouts.common.error')
                        @include('layouts.common.info')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="loans-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Phone No.</th>
                                    <th>Date</th>
                                    <th>Wallet</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Phone No.</th>
                                    <th>Date</th>
                                    <th>Wallet</th>
                                    <th>Actions</th>
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




    <div class="modal fade" id="recon-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Reconcile </span> B2C transaction</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{url('/recon/b2c/update')}}" id="recon-form" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>



                        <div class="row">

                            <div class="col-md-12">
                                <h4><small>Please confirm the transaction status with M-Pesa till report before reconciling</small></h4>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label class="control-label" for="msisdn">Phone Number</label>
                                    <input type="text" class="form-control" id="msisdn" name="msisdn" readonly />
                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="dropdown bootstrap-select show-tick">
                                    <select class="selectpicker" data-style="select-with-transition" title="Select Transaction Status" tabindex="-98"
                                            name="status" id="status" required>
                                        <option value="SUCCEEDED">SUCCEEDED</option>
                                        <option value="FAILED">FAILED</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="transaction_code">Transaction Code</label>
                                    <input type="text" value="{{ old('transaction_code') }}" class="form-control" id="transaction_code" name="transaction_code"  />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="recipient_name">Recipient Name</label>
                                    <input type="text" value="{{ old('recipient_name') }}" class="form-control" id="recipient_name" name="recipient_name"  />
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="id" id="id">


                        <div class="form-group">
                            <button class="btn btn-success btn-block" id="save-brand"><i class="material-icons">save</i> Reconcile</button>
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
