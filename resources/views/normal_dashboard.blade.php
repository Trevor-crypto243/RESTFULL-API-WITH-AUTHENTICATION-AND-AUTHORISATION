@extends('layouts.app')
@section('title', 'Welcome to Quicksava')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="card">
                <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">info</i>
                    </div>
                    {{--<h4 class="card-title">Routes</h4>--}}
                </div>
                <div class="card-body text-center">
                    <h1>Welcome to Quicksava</h1>
                    <h5>Welcome {{auth()->user()->name}}, you are logged in as {{optional(auth()->user()->role)->name}}. Please use the menu on the left to navigate</h5>
                </div>
            </div>
        </div>
    </div>
@endsection
