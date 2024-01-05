@extends('layouts.app')
@section('title', 'Add Logbook Loan Applicant')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header card-header-icon card-header-rose">
                        <div class="card-icon">
                            <i class="material-icons">perm_identity</i>
                        </div>
                        <h4 class="card-title">Add Applicant -
                            <small class="category">Add Logbook Loan Applicant</small>
                        </h4>
                    </div>
                    <div class="card-body">
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')
                        <form action="{{ url('/auto/add-applicant') }}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            {{ method_field('POST') }}

                            <div class="row">    
     
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="selectedUser" class="">Select User</label>
                                        <input type="text" class="form-control" list="userList" name="selectedUser" value="" autocomplete="off">
                                        <datalist id="userList">                
                                            @foreach($users as $user)
                                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                                            @endforeach
                                        </datalist>
                                    </div>
                                </div>


                                <!-- <div class="col-md-4">
                                    <div class="form-group">
                                        <select class="selectpicker" data-style="select-with-transition" title="Select Applicant Type" tabindex="-98"
                                                name="applicant_type" id="applicant_type" required>
                                            <option value="COMPANY">COMPANY</option>
                                            <option value="INDIVIDUAL">INDIVIDUAL</option>                                          
                                        </select>
                                    </div>
                                </div> -->
<!-- 
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select class="selectpicker" data-style="select-with-transition" title="Select Status" tabindex="-98"
                                                name="status" id="status" required>
                                            <option value="NEW">NEW</option>
                                            <option value="IN REVIEW">IN REVIEW</option>
                                            <option value="AMENDMENT">AMENDMENT</option>
                                            <option value="OFFER">OFFER</option>
                                            <option value="ACTIVE">ACTIVE</option>
                                            <option value="REJECTED">REJECTED</option>
                                            <option value="PAID">PAID</option>
                                        </select>
                                    </div>
                                </div>
                                 -->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label id="phone-no">Requested Amount</label>
                                        <input type="number" name="requested_amount" id="requested-amount" value="" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label >Period</label>
                                        <input type="number" name="payment_period" id="payment_period" value="" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label >KRA pin</label>
                                        <input type="string" name="personal_kra_pin" id="personal_kra_pin" value="" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="loan_purpose">Loan purpose</label>
                                        <input type="text" name="loan_purpose"  id="loan_purpose" value="" class="form-control" required>
                                    </div>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-rose pull-right">Submit</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@endsection
