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
    <link href="{{ url('assets/css/material-dashboard.css?v=2.0.2') }}" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    {{--    <link href="{{ url('assets/demo/demo.css') }}" rel="stylesheet" />--}}
    {{--datepicker css--}}
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>
    {{--Select2 CSS--}}
    {{--<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />--}}
    {{--    <link href="{{ url("extras/css/select2-materialize.css")}} " rel="stylesheet">--}}
    {{--my css--}}
    {{--    <link href="{{ url('css/my-style.css') }}" rel="stylesheet"/>--}}
    @stack('css')
</head>

<body>
<div class="wrapper">
    @include('layouts.partials.sidebar')
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
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/') }}">
                                <i class="material-icons">dashboard</i>
                                <p class="d-lg-none d-md-block">
                                    Stats
                                </p>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link" href="#pablo" id="accountLinkOptions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="material-icons">person</i>
                                <p class="d-lg-none d-md-block">
                                    Account
                                </p>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="accountLinkOptions">
                                <a class="dropdown-item" href="{{ url('/') }}">Home</a>
                                {{--                        <a class="dropdown-item" href="{{ url('my-profile') }}">My Profile</a>--}}
                                <a class="dropdown-item" href="{{ url('edit-profile') }}">Edit Profile</a>
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


        <div class="content" style="margin-top: 40px">
            @yield('content')
        </div>
        @include('layouts.partials.footer')
    </div>




    {{--Global modals--}}
    <div class="modal fade" id="org-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel"><span id="survey-modal-title">Add </span> Organisation</h4>
                </div>
                <div class="modal-body" >
                    <form action="{{ url('organisation') }}" method="post" id="survey-form"  enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" id="spoof-input" value="PUT" disabled/>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="business_name">Business Name <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" id="business_name" name="business_name" required/>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="business_address">Business Address <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" id="business_address" name="business_address" required/>
                                </div>
                            </div>


                        </div>



                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="business_reg_no">Business Registration No. <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" id="business_reg_no" name="business_reg_no" required/>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="business_kra_pin">Business KRA PIN <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" id="business_kra_pin" name="business_kra_pin" required/>
                                </div>
                            </div>

                        </div>



                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="business_email">Business E-Mail <span style="color: red">*</span></label>
                                    <input type="email" class="form-control" id="business_email" name="business_email" required/>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="control-label" for="business_phone_no">Business Phone No. <span style="color: red">*</span></label>
                                    <input type="number" class="form-control" id="business_phone_no" name="business_phone_no" required/>
                                </div>
                            </div>

                        </div>


                        <input type="hidden" name="id" id="id"/>
                        <div class="form-group">
                            <button type="button" class="btn btn-default" data-dismiss="modal"><i class="material-icons">close</i> Close</button>
                            <button class="btn btn-success" id="save-brand"><i class="material-icons">save</i> Save</button>
                        </div>
                    </form>
                </div>

                <!--<div class="modal-footer">-->
                <!---->
                <!--</div>-->
            </div>
        </div>
    </div>



</div>
</body>
<!--   Core JS Files   -->
<script src="{{ url('assets/js/core/jquery.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/core/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/core/bootstrap-material-design.min.js') }}" type="text/javascript"></script>
<script src="{{ url('assets/js/plugins/perfect-scrollbar.jquery.min.js') }}"></script>
<!-- Plugin for the momentJs  -->
<script src="{{ url('assets/js/plugins/moment.min.js') }}"></script>
<meta name="csrf-token" content="{{ csrf_token() }}" />

<!--  Plugin for Sweet Alert -->
<script src="{{ url('assets/js/plugins/sweetalert2.js') }}"></script>
<!-- Forms Validations Plugin -->
<script src="{{ url('assets/js/plugins/jquery.validate.min.js') }}"></script>
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="{{ url('assets/js/plugins/jquery.bootstrap-wizard.js') }}"></script>
<!--	Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
<script src="{{ url('assets/js/plugins/bootstrap-selectpicker.js') }}"></script>
<!--  Plugin for the DateTimePicker, full documentation here: https://eonasdan.github.io/bootstrap-datetimepicker/ -->
<script src="{{ url('assets/js/plugins/bootstrap-datetimepicker.min.js') }}"></script>
<!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
<script src="{{ url('assets/js/plugins/jquery.dataTables.min.js') }}"></script>
<!--	Plugin for Tags, full documentation here: https://github.com/bootstrap-tagsinput/bootstrap-tagsinputs  -->
<script src="{{ url('assets/js/plugins/bootstrap-tagsinput.js') }}"></script>
<!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
<script src="{{ url('assets/js/plugins/jasny-bootstrap.min.js') }}"></script>
<!--  Full Calendar Plugin, full documentation here: https://github.com/fullcalendar/fullcalendar    -->
<script src="{{ url('assets/js/plugins/fullcalendar.min.js') }}"></script>
<!-- Vector Map plugin, full documentation here: http://jvectormap.com/documentation/ -->
<script src="{{ url('assets/js/plugins/jquery-jvectormap.js') }}"></script>
<!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
<script src="{{ url('assets/js/plugins/nouislider.min.js') }}"></script>
<!-- Include a polyfill for ES6 Promises (optional) for IE11, UC Browser and Android browser support SweetAlert -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
<!-- Library for adding dinamically elements -->
<script src="{{ url('assets/js/plugins/arrive.min.js') }}"></script>
<!--  Google Maps Plugin    -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA50nWlMzxA5OEFPJLoba4oAGvGI40k6Jc"></script>
<!-- Chartist JS -->
<script src="{{ url('assets/js/plugins/chartist.min.js') }}"></script>
<!--  Notifications Plugin    -->
<script src="{{ url('assets/js/plugins/bootstrap-notify.js') }}"></script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="{{ asset('assets/js/material-dashboard.min.js?v=2.0.2') }}" type="text/javascript"></script>
{{--datepicker js--}}
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>

<!-- Material Dashboard DEMO methods, don't include it in your project! -->
<script src="{{ asset('assets/demo/demo.js') }}"></script>
{{--Select2 JS--}}
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>--}}
{{--<script src="{{ url('js/my-toast.js') }}"></script>--}}
{{--<script src="{{ url('js/my-select2.js') }}"></script>--}}
{{--<script src="{{ url('js/DataTableConfigObj.js') }}"></script>--}}
{{--<script src="{{ url('js/my-components.js') }}"></script>--}}
{{--<script src="{{ url('js/plugins/axios.min.js') }}"></script>--}}
<script>
    $(document).ready(function() {

        $('.datepicker').datetimepicker({
            format: 'YYYY-MM-DD'
        });


    });
</script>
@yield('scripts')
@stack('js')
</html>
