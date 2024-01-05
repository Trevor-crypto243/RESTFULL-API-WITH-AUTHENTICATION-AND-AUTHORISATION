@extends('layouts.app')
@section('title', 'C2B Recons')
@push('js')
    <script>

        const phoneInputField = document.querySelector("#phone_no");
        const phoneInput = window.intlTelInput(phoneInputField, {
            initialCountry: "ke",
            hiddenInput: "phone_no",
            utilsScript:
                "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });


        $(function() {
            // server side - lazy loading
            $('#loans-dt').DataTable({
                processing: true, // loading icon
                serverSide: true, // this means the datatable is no longer client side
                ajax: '{{ route('c2b-dt') }}', // the route to be called via ajax
                columns: [ // datatable columns
                    {data: 'id', name: 'id'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'phone_no', name: 'phone_no'},
                    {data: 'type', name: 'phone_no'},
                    {data: 'transaction_code', name: 'transaction_code'},
                    {data: 'amount', name: 'amount'},
                    {data: 'created_by', name: 'created_by'},
                    {data: 'description', name: 'description'},
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
                    searchPlaceholder: "Search C2B",
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
                        <h4 class="card-title">C2B. <small>Manually credit wallets using phone number</small></h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#c2b-modal">
                                <i class="fa fa-plus"></i> Customer C2B Credit
                            </button>
                        </div>

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="loans-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Phone No.</th>
                                    <th>Type</th>
                                    <th>TRX. Code</th>
                                    <th>Amount</th>
                                    <th>Created By</th>
                                    <th>Desc.</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Phone No.</th>
                                    <th>Type</th>
                                    <th>TRX. Code</th>
                                    <th>Amount</th>
                                    <th>Created By</th>
                                    <th>Desc.</th>
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
    <div class="modal fade" id="c2b-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Add </span> New C2B Credit</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('recon/c2b/create') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="amount">Amount</label>
                                    <input type="number" value="{{ old('amount') }}" class="form-control pb-0 mt-2" name="amount" id="amount" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="transaction_code">Transaction Code</label>
                                    <input type="text" value="{{ old('transaction_code') }}" class="form-control pb-0 mt-2" name="transaction_code" id="transaction_code"/>
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

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="dropdown bootstrap-select show-tick">
                                        <select class="selectpicker" data-style="select-with-transition" title="Choose Type" tabindex="-98"
                                                name="type" id="type" required>
                                            <option value="M-PESA">M-PESA</option>
                                            <option value="CHEQUE">CHEQUE</option>
                                            <option value="CASH">CASH</option>
                                            <option value="DIRECT DEPOSIT">DIRECT DEPOSIT</option>
                                            <option value="EFT">EFT</option>
                                            <option value="SWIFT TRANSFER">SWIFT TRANSFER</option>
                                            <option value="RTGS">RTGS</option>
                                        </select>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="description">Description</label>
                                    <input type="text" value="{{ old('description') }}" class="form-control" id="description" name="description" required />
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Save</button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>




@endsection
