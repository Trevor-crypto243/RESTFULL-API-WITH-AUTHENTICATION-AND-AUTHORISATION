@extends('layouts.app')
@section('title', 'Loan Product')


@push('js')
    <script>
        // delete javascript
        $('.delete-model-form').on('submit', function() {
            if (confirm('Are you sure you want to delete this fee?')) {
                return true;
            }
            return false;
        });

        $('.delete-product-form').on('submit', function() {
            if (confirm('Are you sure you want to delete this product from the organisation?')) {
                return true;
            }
            return false;
        });

        $(document).on('click', '.edit-matrix-btn', function() {
            var _Btn = $(this);
            var _id = _Btn.attr('acs-id'),
                _Form = $('#matrix-form');

            if (_id !== '') {
                $.ajax({
                    url: _Btn.attr('source'),
                    type: 'get',
                    dataType: 'json',
                    beforeSend: function() {
                        // _ModalTitle.text('Edit');
                        // _SpoofInput.removeAttr('disabled');
                    },
                    success: function(data) {
                        console.log(data);
                        // populate the modal fields using data from the server
                        $('#period').val(data['loan_period']);
                        $('#new_client_interest').val(data['new_client_interest']);
                        $('#existing_client_interest').val(data['existing_client_interest']);
                        $('#interest_rate_matrix_id').val(data['id']);

                        // set the update url
                        var action =  _Form .attr('action');
                        // action = action + '/' + season_id;
                        console.log(action);
                        _Form .attr('action', action);

                        // open the modal
                        $('#matrix-modal').modal('show');
                    }
                });
            }
        });

    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8" >
                <div class="card" style="margin-top: 0px">
                    <div class="card-header card-header-icon card-header-primary">
                        <div class="card-icon">
                            <i class="material-icons">money</i>
                        </div>
                        <h4 class="card-title">{{$loanProduct->name}}
{{--                            <small class="category">Update your profile</small>--}}
                        </h4>
                    </div>
                    <div class="card-body">
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <div class="row">
                            <div class="col-md-5">
                                <ul class="list-group">
                                    <li class="list-group-item"><span class="text-muted">Max Period:</span> {{$loanProduct->max_period_months}} Months</li>
                                    <li class="list-group-item"><span class="text-muted">Fee Application:</span> {{$loanProduct->fee_application}}</li>
{{--                                    <li class="list-group-item"><span class="text-muted">Monthly Interest Rate:</span> {{$loanProduct->interest_rate}}%</li>--}}
                                    <li class="list-group-item"><span class="text-muted">Total Loans:</span> {{\App\LoanRequest::where('loan_product_id',$loanProduct->id)->where('approval_status','APPROVED')->count()}}</li>
                                    <li class="list-group-item"> <span class="text-muted">Description:</span> <br> {{$loanProduct->description}}</li>
                                    <li class="list-group-item"> <span class="text-muted">Min Amount:</span> KES {{number_format($loanProduct->min_amount)}}</li>
                                    <li class="list-group-item"> <span class="text-muted">Max Amount:</span> KES {{number_format($loanProduct->max_amount)}}</li>
                                </ul>


                                <ul class="nav">
                                  <li class="nav-item" >
                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#update-limits-modal">
                                            Update Min/Max
                                        </button>
                                  </li>

                                    <li class="nav-item" >
                                        <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#update-period-modal">
                                            Update Period
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-md-7">
                                <ul class="list-group">
                                    <li class="list-group-item"><span class="info-text">Interest Matrix</span> </li>

                                    <table class="table table-no-bordered table-hover">
                                        <thead >
                                        <tr>
                                            <th>Period</th>
                                            <th>New Client</th>
                                            <th>Existing Client</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($loanProduct->matrices as $matrix)
                                            <tr>
                                                <td style="padding: 0px; padding-right: 20px; margin: 0px">{{$matrix->loan_period}}</td>
                                                <td style="padding: 0px; margin: 0px">{{$matrix->new_client_interest}} %</td>
                                                <td style="padding: 0px; margin: 0px">{{$matrix->existing_client_interest}} %</td>
                                                <td style="padding: 0px; margin: 0px">
                                                    <button source="{{route('edit-matrix' ,  $matrix->id) }}" acs-id="{{$matrix->id}}" class="btn btn-success btn-sm edit-matrix-btn"><i class="fa fa-pencil"></i> Edit</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>


                                    <form action="{{url('/products/closing/update')}}" method="post" class="form-horizontal mt-3" enctype="multipart/form-data">
                                        {{ csrf_field() }}
                                        {{--spoofing--}}

                                        <input type="hidden" name="product_id" value="{{$loanProduct->id}}">


                                        <div class="row" style="margin-left: 0px; margin-right: 0px">

                                            <div class=" col-md-4">
                                                <p >Closing Date</p>
                                            </div>

                                            <div class=" col-md-4">
                                                <input id="closing_date" type="number" max="31" min="1" class="form-control pb-0" value="{{$loanProduct->closing_date}}" name="closing_date" placeholder="Closing date (1-31)" required/>
                                            </div>

                                            <div class="col-md-4">
                                                <button class="btn btn-success btn-sm" id="save-brand"><i class="material-icons">save</i> Update</button>

                                            </div>


                                        </div>



                                    </form>

                                </ul>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="card">
                    <div class="card-header card-header-icon card-header-primary">
                        <div class="card-icon">
                            <i class="material-icons">money</i>
                        </div>
                        <h4 class="card-title">Fees -
                            <small class="category">Applicable loan product fees</small>
                        </h4>
                    </div>
                    <div class="card-body">

                        <div class="material-datatables">
                            <table id="loans-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Frequency</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($loanProduct->fees as $fee)
                                    <tr>
                                        <td>{{$fee->name}}</td>
                                        <td>{{$fee->amount_type == 'PERCENTAGE' ? $fee->amount.'%' : 'KES '. number_format($fee->amount)}} </td>
                                        <td>{{$fee->frequency}}</td>
                                        <td>
                                            <form action="{{ url('products/loans/fees/delete') }}" method="post" style="display: inline;" class="delete-model-form">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="id" value="{{$fee->id}}">
                                                <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                            </form>

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Frequency</th>
                                    <th>Action</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>

            </div>


            <div class="col-md-4">
                <div class="card card-blog" style="margin-top: 0px">

                    <div class="card-body">
                        <h6 class="card-category text-gray">Manage Fees</h6>
                        <h4 class="card-title">Add Loan Product Fee</h4>

                        <p>You can add any fees that are applicable to this loan product</p>
                        <p >Fee details</p>

                        <form action="{{url('/products/loans/fees/create')}}" method="post" class="form-horizontal" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            {{--spoofing--}}

                            <input type="hidden" name="loan_product_id" value="{{$loanProduct->id}}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label" for="name">Fee Name</label>
                                        <input type="text" value="{{ old('name') }}" class="form-control" id="name" name="name" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label" for="amount">Amount</label>
                                        <input type="number" value="{{ old('amount') }}" class="form-control" id="amount" step=".01" min="0" name="amount" required />
                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <select class="selectpicker" data-style="select-with-transition" title="Amount Type" tabindex="-98"
                                                name="amount_type" id="amount_type" required>
                                            <option value="PERCENTAGE">PERCENTAGE</option>
                                            <option value="AMOUNT">ACTUAL AMOUNT</option>
                                        </select>
                                    </div>
                                </div>
                            </div>



                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group ">
                                        <select class="selectpicker" data-style="select-with-transition" title="Application Frequency" tabindex="-98"
                                                name="frequency" id="frequency" required>
                                            <option value="MONTHLY">MONTHLY</option>
                                            <option value="ONE-OFF">ONE-OFF</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row" >

                                <div class="col-md-12">
                                    <button class="btn btn-success btn-sm btn-block" id="save-brand"><i class="material-icons">save</i> Create Fee</button>

                                </div>
                            </div>

                        </form>

                    </div>
                </div>

                <div class="card card-blog" style="margin-top: 0px">

                    <div class="card-body">
                        <h6 class="card-category text-gray">Organisations</h6>
                        <p>Assign this product to organisations to make it available for their customers</p>
                        <p >Select Organisation/employer</p>

                        <form action="{{url('products/organisations/add')}}" method="post" class="form-horizontal" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            {{--spoofing--}}

                            <input type="hidden" name="loan_product_id" value="{{$loanProduct->id}}">

                            <select class="selectpicker" data-style="select-with-transition" title="Organisation" tabindex="-98"
                                    name="organisation_id" id="organisation_id" required>
                                @foreach(\App\Employer::all() as $employer)
                                    <option value="{{$employer->id}}">{{$employer->business_name}}</option>
                                @endforeach
                            </select>

                            <button class="btn btn-success btn-sm" id="save-brand"><i class="material-icons">save</i> Add</button>

                        </form>

                        <table class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                            <thead>
                            <tr>
                                <th>Org</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(\App\EmployerLoanProduct::where('loan_product_id',$loanProduct->id)->get() as $employer)
                                <tr>
                                    <td>{{optional($employer->employer)->business_name}}</td>
                                    <td>
                                        <form action="{{ url('products/organisations/delete') }}" method="post" style="display: inline;" class="delete-product-form">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="id" value="{{$employer->id}}">
                                            <button class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Delete</button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Org</th>
                                <th>Action</th>
                            </tr>
                            </tfoot>
                        </table>

                    </div>
                </div>

            </div>
        </div>

    </div>




    {{--modal--}}
    <div class="modal fade" id="update-limits-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span >Update </span> Min/Max amount</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('products/limits/update') }}" method="post" id="matrix-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="min_amount">Min Amount</label>
                                    <input type="number" step=".01" class="form-control pb-0 mt-2" name="min_amount" id="min_amount" value="{{$loanProduct->min_amount}}" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="max_amount">Max Amount</label>
                                    <input type="number" step=".01" class="form-control pb-0 mt-2" name="max_amount" id="max_amount" value="{{$loanProduct->max_amount}}" required/>
                                </div>
                            </div>

                        </div>


                        <input type="hidden" name="product_id" value="{{$loanProduct->id}}"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Update</button>
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


    <div class="modal fade" id="update-period-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span >Update </span> Maximum loan period</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('products/period/update') }}" method="post" id="matrix-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>


                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="min_amount">Period (Months)</label>
                                    <input type="number" step=".01" class="form-control pb-0 mt-2" name="period" id="period" value="{{$loanProduct->max_period_months}}" required/>
                                </div>
                            </div>

                        </div>


                        <input type="hidden" name="product_id" value="{{$loanProduct->id}}"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Update</button>
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


    <div class="modal fade" id="matrix-modal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"> <span id="user-modal-title">Update </span> Interest Matrix</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('products/interest/matrix/update') }}" method="post" id="matrix-form" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        {{--spoofing--}}
                        <input type="hidden" name="_method" id="user-spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label" for="period">PERIOD</label>
                                    <input type="text" disabled  class="form-control" id="period" name="period" required />
                                </div>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="new_client_interest">New Client</label>
                                    <input type="number" step=".01" class="form-control pb-0 mt-2" name="new_client_interest" id="new_client_interest" required/>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="existing_client_interest">Existing Client</label>
                                    <input type="number" step=".01" class="form-control pb-0 mt-2" name="existing_client_interest" id="existing_client_interest" required/>
                                </div>
                            </div>

                        </div>


                        <input type="hidden" name="interest_rate_matrix_id" id="interest_rate_matrix_id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Update</button>
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
