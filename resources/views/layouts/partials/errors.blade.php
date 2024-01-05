<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>@yield('title') | {{ config('app.name') }}</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <!-- CSS Files -->
    <link href="{{ asset('assets/css/material-dashboard.css?v=2.0.2') }}" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
{{--    <link href="{{ asset('assets/demo/demo.css') }}" rel="stylesheet" />--}}
    {{--datepicker css--}}
{{--    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />--}}
    {{--Select2 CSS--}}
    {{--<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />--}}
    <link href="{{ asset("extras/css/select2-materialize.css")}} " rel="stylesheet">
    {{--my css--}}
    <link href="{{ asset('css/my-style.css') }}" rel="stylesheet"/>

{{--    <link rel="stylesheet" href="{{ asset('assets/dropzone/dropzone.css') }}" />--}}
{{--    <script src="{{ asset('assets/dropzone/dropzone.js') }}"></script>--}}


    @stack('css')
</head>

<body>
<div class="wrapper">
    <div class="sidebar" data-color="purple" data-background-color="white" data-image="">
        <!--
          Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

          Tip 2: you can also add an image using data-image tag
      -->
        <div class="logo">
            <a href="{{url('/')}}" class="simple-text logo-mini">
                QS
            </a>
            <a href="{{url('/')}}" class="simple-text logo-normal">
                Quicksava
            </a>
        </div>
        <div class="sidebar-wrapper">
            <ul class="nav">
                <li class="nav-item {{ \Request::is('/') ? 'active' : '' }} ">
                    <a class="nav-link" href="{{url('/')}}">
                        <i class="material-icons">dashboard</i>
                        <p> Dashboard </p>
                    </a>
                </li>

            </ul>
        </div>
    </div>
    <div class="main-panel">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top d-print-none" id="navigation-example">
            <div class="container-fluid">
                <div class="navbar-wrapper">
                    <div class="navbar-minimize">
                        <button id="minimizeSidebar" class="btn btn-just-icon btn-white btn-fab btn-round">
                            <i class="material-icons text_align-center visible-on-sidebar-regular">more_vert</i>
                            <i class="material-icons design_bullet-list-67 visible-on-sidebar-mini">view_list</i>
                        </button>
                    </div>
                    <a class="navbar-brand" href="#">@yield('title')</a>
                </div>
                <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation" data-target="#navigation-example">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                    <span class="navbar-toggler-icon icon-bar"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end">

                    <ul class="navbar-nav">

                        <li class="nav-item dropdown">
                            <p class="d-lg-block d-md-block">
{{--                                {{ auth()->user()->name }}--}}
                            </p>
                        </li>


                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#" id="accountLinkOptions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="material-icons">person</i>
                                <p class="d-lg-none d-md-block">
                                    Account
                                </p>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="accountLinkOptions">
                                <a class="dropdown-item" href="{{ url('/') }}">Home</a>
                                <a class="dropdown-item" href="{{ url('profile') }}">My Profile</a>
                                {{--<a class="dropdown-item" href="{{ url('edit-profile') }}">Edit Profile</a>--}}
                                <a class="dropdown-item portal-logout" href="{{url('logout')}}">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        @push('js')
            <script>
                $('li.child.active:first').parents('li').addClass('active');
                $('li.child.active:first').parents('div.collapse').addClass('in');

                $('.portal-logout').on('click', function() {
                    $('#portal-logout-form').submit();
                });
            </script>
        @endpush
        <div class="content">
            @yield('content')
        </div>
        @include('layouts.partials.footer')
    </div>
</div>
</body>
<!--   Core JS Files   -->
<script src="{{ asset('assets/js/core/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/core/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/core/bootstrap-material-design.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
<!-- Plugin for the momentJs  -->
<script src="{{ asset('assets/js/plugins/moment.min.js') }}"></script>
<!--  Plugin for Sweet Alert -->
<script src="{{ asset('assets/js/plugins/sweetalert2.js') }}"></script>
<!--  Plugin for bootbox -->
<script src="{{asset('assets/js/plugins/bootbox.min.js')}}"></script>

<!-- Forms Validations Plugin -->
{{--<script src="{{ asset('assets/js/plugins/jquery.validate.min.js') }}"></script>--}}
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
{{--<script src="{{ asset('assets/js/plugins/jquery.bootstrap-wizard.js') }}"></script>--}}
<!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="{{ asset('assets/js/plugins/bootstrap-selectpicker.js') }}"></script>
<!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
<script src="{{ asset('assets/js/plugins/bootstrap-datetimepicker.min.js') }}"></script>
<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="{{ asset('assets/js/plugins/jquery.dataTables.min.js') }}"></script>
<!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
<script src="{{ asset('assets/js/plugins/bootstrap-tagsinput.js') }}"></script>
<!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
{{--<script src="{{ asset('assets/js/plugins/jasny-bootstrap.min.js') }}"></script>--}}
<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
{{--<script src="{{ asset('assets/js/plugins/fullcalendar.min.js') }}"></script>--}}
<!-- Vector Map plugin, full documentation here: http://jvectormap.com/documentation/ -->
{{--<script src="{{ asset('assets/js/plugins/jquery-jvectormap.js') }}"></script>--}}
<!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
{{--<script src="{{ asset('assets/js/plugins/nouislider.min.js') }}"></script>--}}
<!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
<!-- Library for adding dinamically elements -->
<script src="{{ asset('assets/js/plugins/arrive.min.js') }}"></script>
<!--  Google Maps Plugin    -->
{{--<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script>--}}
<!-- Chartist JS -->
{{--<script src="{{ asset('assets/js/plugins/chartist.min.js') }}"></script>--}}
<!--  Notifications Plugin    -->
<script src="{{ asset('assets/js/plugins/bootstrap-notify.js') }}"></script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="{{ asset('assets/js/material-dashboard.js') }}" type="text/javascript"></script>
{{--datepicker js--}}
{{--<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>--}}
{{--<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>--}}
<!-- Material Dashboard DEMO methods, don't include it in your project! -->
{{--<script src="{{ asset('assets/demo/demo.js') }}"></script>--}}
{{--Select2 JS--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="{{ asset('js/my-toast.js') }}"></script>
<script src="{{ asset('js/my-select2.js') }}"></script>
<script src="{{ asset('js/DataTableConfigObj.js') }}"></script>
<script src="{{ asset('js/my-components.js') }}"></script>
<script src="{{ asset('js/plugins/axios.min.js') }}"></script>

    @yield('scripts')
<script>


    $(document).ready(function() {

        // $('.datepicker').datetimepicker({
        //     format: 'YYYY-MM-DD'
        // });

        $('.select2').select2();


    });
</script>
<script>
</script>
@stack('js')
</html>
