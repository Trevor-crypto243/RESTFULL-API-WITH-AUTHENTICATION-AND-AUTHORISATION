@extends('layouts.partials.errors')
@section('title', '404 - Not Found')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="card">
                <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">warning</i>
                    </div>
                    {{--<h4 class="card-title">Routes</h4>--}}
                </div>
                <div class="card-body text-center">
                    <h1>404 - Not Found</h1>
{{--                    <h5>{{ $exception->getMessage() }}</h5>--}}
                    <a href="{{ url('/') }}" class="btn btn-primary">Home</a>
                </div>
            </div>
        </div>
    </div>
@endsection
