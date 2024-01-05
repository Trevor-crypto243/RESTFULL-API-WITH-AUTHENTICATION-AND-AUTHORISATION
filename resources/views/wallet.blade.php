@extends('layouts.app')
@section('title', 'Wallet - '.$owner)
@push('js')
    <script>


        var disburseDt = $('#transactions-dt').DataTable({
            processing: true, // loading icon
            serverSide: false, // this means the datatable is no longer client side
            ajax: $('#transactions-dt').data('source'), // the route to be called via ajax
            columns: [ // datatable columns
                {data: 'id', name: 'id'},
                {data: 'amount', name: 'amount'},
                {data: 'previous_balance', name: 'previous_balance'},
                {data: 'transaction_type', name: 'transaction_type'},
                {data: 'source', name: 'source'},
                {data: 'trx_id', name: 'trx_id'},
                {data: 'created_at', name: 'created_at'},
                {data: 'narration', name: 'narration'},
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
                searchPlaceholder: "Search Transactions",
            },
            "order": [[0, "desc"]]
        });

        $('.activate-wallet-form').on('submit', function() {
            if (confirm('Are you sure you want to ACTIVATE this wallet and allow user to withdraw funds?')) {
                return true;
            }
            return false;
        });

        $('.freeze-wallet-form').on('submit', function() {
            if (confirm('Are you sure you want to FREEZE this wallet? User won\'t be able to withdraw money')) {
                return true;
            }
            return false;
        });
    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="total_customers" style="padding-bottom: 20px">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">functions</i>
                        </div>
                        <p class="card-category">Wallet Balance</p>
                        <h4 class="card-title">{{$wallet->currency}} {{number_format($wallet->current_balance)}}</h4>
                    </div>

                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="checkoff_customers" style="padding-bottom: 20px">
                    <div class="card-header card-header-icon card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">restore</i>
                        </div>
                        <p class="card-category">Previous Balance</p>
                        <h4 class="card-title"> {{$wallet->currency}} {{number_format($wallet->previous_balance)}}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="approved_today" style="padding-bottom: 20px">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">currency_exchange</i>
                        </div>
                        <p class="card-category">Currency</p>
                        <h4 class="card-title"> {{$wallet->currency}}</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats" id="paid_today" style="padding-bottom: 20px">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">info</i>
                        </div>
                        <p class="card-category">Status</p>
                        <h4 class="card-title" id="paidToday"> {{$wallet->active == true ? 'ACTIVE' : 'FROZEN'}}</h4>
                    </div>

                </div>
            </div>


            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">account_balance_wallet</i>
                        </div>
                        <h4 class="card-title">{{$owner}} - <small>Wallet Transaction History</small></h4>
                    </div>
                    <div class="card-body">

                        <div class="toolbar">

                            @if(auth()->user()->role->has_perm([39]))
                                @if($wallet->active == true)
                                    <form action="{{ url('wallet/freeze') }}" method="post" style="display: inline;"
                                          class="freeze-wallet-form">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="id" value="{{$wallet->id}}">
                                        <input type="hidden" name="owner" value="{{$owner}}">
                                        <button class="btn btn-warning btn-sm">Freeze Wallet</button>
                                    </form>
                                @else
                                    <form action="{{ url('wallet/activate') }}" method="post" style="display: inline;"
                                          class="activate-wallet-form">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="id" value="{{$wallet->id}}">
                                        <input type="hidden" name="owner" value="{{$owner}}">
                                        <button class="btn btn-success btn-sm">Activate Wallet</button>
                                    </form>
                                @endif
                            @endif

                            @if(auth()->user()->role->has_perm([27]))
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#withdraw-modal">
                                        <i class="material-icons">arrow_circle_down</i> M-Pesa Withdraw
                                    </button>
                            @endif


                        </div>




                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')


                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="transactions-dt" data-source="{{ route('transactions-dt',$wallet->id) }}" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Prev. Bal</th>
                                    <th>Type</th>
                                    <th>Source</th>
                                    <th>TRX. ID</th>
                                    <th>Date</th>
                                    <th>Narration</th>
                                </tr>
                                </thead>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Prev. Bal</th>
                                    <th>Type</th>
                                    <th>Source</th>
                                    <th>TRX. ID</th>
                                    <th>Date</th>
                                    <th>Narration</th>
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
    <div class="modal fade" id="withdraw-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Force Withdraw to </span> M-Pesa</h4>
                </div>
                <div class="modal-body" >
                    <form id="userform" action="{{ url('wallet/withdraw') }}" method="post" id="user-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>


                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="amount">Amount</label>
                                    <input type="number" value="{{ old('amount') }}" class="form-control pb-0 mt-2" name="amount" id="amount" required/>
                                </div>
                            </div>
                            <input type="hidden" name="wallet_id" value="{{$wallet->id}}">
                        </div>


                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Send</button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>



@endsection
