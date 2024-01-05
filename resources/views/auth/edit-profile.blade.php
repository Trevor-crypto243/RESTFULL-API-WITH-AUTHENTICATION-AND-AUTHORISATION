@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-icon card-header-rose">
                        <div class="card-icon">
                            <i class="material-icons">perm_identity</i>
                        </div>
                        <h4 class="card-title">Edit Profile -
                            <small class="category">Update your profile</small>
                        </h4>
                    </div>
                    <div class="card-body">
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')
                        <form action="{{ url('edit-profile') }}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="surname">Name</label>
                                        <input type="text" name="name"  id="name" value="{{ $user->name }}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="surname">Surname</label>
                                        <input type="text" name="surname"  id="surname" value="{{ $user->surname }}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label id="email">Email Address</label>
                                        <input type="email" name="email"  id="email" value="{{ $user->email }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label id="phone-no">Phone No</label>
                                        <input type="text" name="phone_no" id="phone-no" value="{{ $user->phone_no }}" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label >ID. No</label>
                                        <input type="text" name="id_no" id="id_no" value="{{ $user->id_no }}" class="form-control" required>
                                    </div>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-rose pull-right">Update Profile</button>
                            <div class="clearfix"></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-profile">
                    <div class="card-avatar">
                        <a href="#pablo">
                            <img class="img" src="{{ is_null( auth()->user()->photo) ?  $photo :  auth()->user()->photo  }}" />
                        </a>
                    </div>
                    <div class="card-body">
                        <h6 class="card-category text-gray">{{ $user->role->role_name }}</h6>
                        <h4 class="card-title">{{ $user->name }}</h4>
                    </div>
                </div>

{{--                <div class="card card-profile">--}}
{{--                    <div class="card-body">--}}
{{--                        @if(is_null($user->isAdminOrUser($user)))--}}
{{--                            <h6 class="card-category text-gray">--}}
{{--                                You have not been assigned to a merchant yet. Please contact administrator--}}
{{--                            </h6>--}}

{{--                        @else--}}
{{--                            <h6 class="card-category text-gray">{{ optional($user->isAdminOrUser($user)->merchant)->name }}</h6>--}}
{{--                            <h4 class="card-title">{{ optional($user->isAdminOrUser($user)->merchant)->description }}</h4>--}}
{{--                        @endif--}}

{{--                    </div>--}}
{{--                </div>--}}
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header card-header-icon card-header-rose">
                        <div class="card-icon">
                            <i class="material-icons">fingerprint</i>
                        </div>
                        <h4 class="card-title">Change Password -
                            <small class="category">Reset your login password</small>
                        </h4>
                    </div>
                    <div class="card-body">
                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        @include('layouts.common.warning')

                        <form action="{{ url('change-password') }}" method="post" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            {{ method_field('PUT') }}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="current-password">Current Password</label>
                                        <input type="password" name="current_password" id="current-password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="new-password">New Password</label>
                                        <input type="password" name="password" id="new-password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="confirm-password">Re-Enter the new Password</label>
                                        <input type="password" name="password_confirmation" id="confirm-password" class="form-control" required>
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
