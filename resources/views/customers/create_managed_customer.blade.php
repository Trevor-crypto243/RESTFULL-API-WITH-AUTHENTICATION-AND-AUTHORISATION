@extends('layouts.app')
@section('title', 'Managed Customers')
@push('js')
    <script>

        $(document).ready(function() {

            // Initialise the wizard
            // demo.initMaterialWizard();

            // Code for the Validator
            var $validator = $('.card-wizard form').validate({
                rules: {
                    // firstname: {
                    //     required: true,
                    //     minlength: 3
                    // },
                    // lastname: {
                    //     required: true,
                    //     minlength: 3
                    // },
                    // email: {
                    //     required: true,
                    //     minlength: 3,
                    // }
                },

                highlight: function(element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-danger');
                },
                success: function(element) {
                    $(element).closest('.form-group').removeClass('has-danger').addClass('has-success');
                },
                errorPlacement: function(error, element) {
                    $(element).append(error);
                }
            });

            // Wizard Initialization
            $('.card-wizard').bootstrapWizard({
                'tabClass': 'nav nav-pills',
                'nextSelector': '.btn-next',
                'previousSelector': '.btn-previous',

                onNext: function(tab, navigation, index) {
                    var $valid = $('.card-wizard form').valid();
                    if (!$valid) {
                        $validator.focusInvalid();
                        return false;
                    }
                },

                onInit: function(tab, navigation, index) {
                    //check number of tabs and fill the entire row
                    var $total = navigation.find('li').length;
                    var $wizard = navigation.closest('.card-wizard');

                    $first_li = navigation.find('li:first-child a').html();
                    $moving_div = $('<div class="moving-tab">' + $first_li + '</div>');
                    $('.card-wizard .wizard-navigation').append($moving_div);

                    refreshAnimation($wizard, index);

                    $('.moving-tab').css('transition', 'transform 0s');
                },

                onTabClick: function(tab, navigation, index) {
                    var $valid = $('.card-wizard form').valid();

                    if (!$valid) {
                        return false;
                    } else {
                        return true;
                    }
                },

                onTabShow: function(tab, navigation, index) {
                    var $total = navigation.find('li').length;
                    var $current = index + 1;

                    var $wizard = navigation.closest('.card-wizard');

                    // If it's the last tab then hide the last button and show the finish instead
                    if ($current >= $total) {
                        $($wizard).find('.btn-next').hide();
                        $($wizard).find('.btn-finish').show();
                    } else {
                        $($wizard).find('.btn-next').show();
                        $($wizard).find('.btn-finish').hide();
                    }

                    button_text = navigation.find('li:nth-child(' + $current + ') a').html();

                    setTimeout(function() {
                        $('.moving-tab').text(button_text);
                    }, 150);


                    if (index === 2){
                        //alert("HEY");
                        $.ajax({
                            url: '/ajax/customers/managed/otp/'+ document.getElementById("phone_no").value,
                            dataType: 'JSON',
                            type: 'GET',
                            success: function(response) {
                                console.log(response);
                            }
                        });
                    }

                    refreshAnimation($wizard, index);
                }
            });


            // Prepare the preview for profile picture
            $("#wizard-picture").change(function() {
                readURL(this);
            });
            $('.set-full-height').css('height', 'auto');

            //Function to show image before upload

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#wizardPicturePreview').attr('src', e.target.result).fadeIn('slow');
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            $(window).resize(function() {
                $('.card-wizard').each(function() {
                    $wizard = $(this);

                    index = $wizard.bootstrapWizard('currentIndex');
                    refreshAnimation($wizard, index);

                    $('.moving-tab').css({
                        'transition': 'transform 0s'
                    });
                });
            });

            function refreshAnimation($wizard, index) {
                $total = $wizard.find('.nav li').length;
                $li_width = 100 / $total;

                total_steps = $wizard.find('.nav li').length;
                move_distance = $wizard.width() / total_steps;
                index_temp = index;
                vertical_level = 0;

                mobile_device = $(document).width() < 600 && $total > 3;

                if (mobile_device) {
                    move_distance = $wizard.width() / 2;
                    index_temp = index % 2;
                    $li_width = 50;
                }

                $wizard.find('.nav li').css('width', $li_width + '%');

                step_width = move_distance;
                move_distance = move_distance * index_temp;

                $current = index + 1;

                if ($current == 1 || (mobile_device == true && (index % 2 == 0))) {
                    move_distance -= 8;
                } else if ($current == total_steps || (mobile_device == true && (index % 2 == 1))) {
                    move_distance += 8;
                }

                if (mobile_device) {
                    vertical_level = parseInt(index / 2);
                    vertical_level = vertical_level * 38;
                }

                $wizard.find('.moving-tab').css('width', step_width);
                $('.moving-tab').css({
                    'transform': 'translate3d(' + move_distance + 'px, ' + vertical_level + 'px, 0)',
                    'transition': 'all 0.5s cubic-bezier(0.29, 1.42, 0.79, 1)'

                });
            }
            setTimeout(function() {
                $('.card.card-wizard').addClass('active');
            }, 600);
        });

    </script>
@endpush

@section('scripts')
    <script src="{{ url('assets/js/plugins/jquery.bootstrap-wizard.js') }}"></script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="col-md-10 col-12 mr-auto ml-auto">
            <!--      Wizard container        -->

            @include('layouts.common.success')
            @include('layouts.common.warning')
            @include('layouts.common.warnings')

            <div class="wizard-container">
                <div class="card card-wizard" data-color="purple" id="wizardProfile">
                    <form action="{{url('/customers/managed/create')}}" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                        <!--        You can switch " data-color="primary" "  with one of the next bright colors: "green", "orange", "red", "blue"       -->
                        <div class="card-header text-center">
                            <h3 class="card-title">
                               New Customer
                            </h3>
                            <h5 class="card-description">Register and onboard a new Inua customer to an employer</h5>
                        </div>
                        <div class="wizard-navigation">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#about" data-toggle="tab" role="tab">
                                        Biodata
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#employment" data-toggle="tab" role="tab">
                                        Employment
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#verify" data-toggle="tab" role="tab">
                                        Finish
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="about">
                                    <h5 class="info-text"> Let's start with the basic information (biodata)</h5>
                                    <div class="row justify-content-center">
                                        <div class="col-sm-4">
                                            <div class="picture-container">
                                                <div class="picture">
                                                    <img src="{{url('assets/img/default-avatar.png')}}" class="picture-src" id="wizardPicturePreview" title="" />
                                                    <input type="file" name="selfie" id="wizard-picture">
                                                </div>
                                                <h6 class="description">Choose Selfie (Required)</h6>
                                            </div>
                                        </div>

                                        <div class="col-sm-8">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="input-group form-control-lg">
                                                        <div class="form-group">
                                                            <label for="firstname" class="bmd-label-floating">First Name (required)</label>
                                                            <input type="text" class="form-control" id="firstname" value="{{old('firstname')}}" name="firstname" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="input-group form-control-lg">
                                                        <div class="form-group">
                                                            <label for="surname" class="bmd-label-floating">Surname (required)</label>
                                                            <input type="text" class="form-control" id="surname" value="{{old('surname')}}" name="surname" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="input-group form-control-lg">
                                                        <div class="form-group">
                                                            <label for="email" class="bmd-label-floating">Email (Optional)</label>
                                                            <input type="text" class="form-control" name="email" value="{{old('email')}}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6">
                                                    <div class="input-group form-control-lg">
                                                        <div class="form-group">
                                                            <label for="id_no" class="bmd-label-floating">ID Number</label>
                                                            <input type="text" readonly class="form-control" value="{{$id_no}}" name="id_no" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                        <div class="col-lg-4 mt-3">
                                            <div class="input-group form-control-lg">
                                                <div class="form-group">
                                                    <select class="selectpicker" name="gender" required data-style="select-with-transition" title="Select Gender (Required)">
                                                        <option value="MALE"> MALE </option>
                                                        <option value="FEMALE"> FEMALE </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 mt-3">
                                            <div class="input-group form-control-lg">
                                                <div class="form-group">
                                                    <label for="phone_no" >Phone Number (required)</label>
                                                    <input type="tel" class="form-control" name="phone_no" value="{{old('phone_no')}}" id="phone_no" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 mt-3">
                                            <div class="input-group form-control-lg">
                                                <div class="form-group">
                                                    <label for="dob" >Date of Birth (required)</label>
                                                    <input type="text" class="form-control datepicker" value="{{old('dob')}}" name="dob" required>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                                <div class="tab-pane" id="employment">
                                    <div class="row justify-content-center">
                                        <div class="col-sm-12">
                                            <h5 class="info-text"> Employment details </h5>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <select class="selectpicker" name="employer" required data-style="select-with-transition" title="Select Employer (Required)">
                                                    @foreach(\App\Employer::all() as $employer)
                                                        <option value="{{$employer->id}}"> {{$employer->business_name}} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="phone_no" class="bmd-label-floating" >Payroll Number (required)</label>
                                                <input type="text" class="form-control" name="payroll_no" value="{{old('payroll_no')}}" id="payroll_no" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="employment_date">Employment Date (required)</label>
                                                <input type="text" class="form-control datepicker" name="employment_date" value="{{old('employment_date')}}" id="employment_date" required>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mt-2">
                                        <div class="col-sm-4">
                                            <label for="id_front">Select ID Front</label>
                                            <input class="form-control" type="file" id="id_front" required name="id_front">
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="id_front">Select ID Back</label>
                                            <input class="form-control" type="file" id="id_back" required name="id_back">
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="latest_payslip">Select Latest Payslip</label>
                                            <input class="form-control" type="file" id="latest_payslip" required name="latest_payslip">
                                        </div>

                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="nature_of_work" class="bmd-label-floating" >Nature of Work (required)</label>
                                                <input type="text" class="form-control" name="nature_of_work" value="{{old('nature_of_work')}}" id="nature_of_work" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="position" class="bmd-label-floating" >Position (required)</label>
                                                <input type="text" class="form-control" name="position" value="{{old('position')}}" id="position" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="location" class="bmd-label-floating" >Location (required)</label>
                                                <input type="text" class="form-control" name="location" value="{{old('location')}}" id="location" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="gross_salary" class="bmd-label-floating" >Gross Salary (required)</label>
                                                <input type="number" class="form-control" name="gross_salary" value="{{old('gross_salary')}}" id="gross_salary" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="basic_salary" class="bmd-label-floating" >Basic Salary (required)</label>
                                                <input type="number" class="form-control" name="basic_salary" value="{{old('basic_salary')}}" id="basic_salary" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="form-group">
                                                <label for="net_salary" class="bmd-label-floating"  >Net Salary (required)</label>
                                                <input type="number" class="form-control" name="net_salary" value="{{old('net_salary')}}" id="net_salary" required>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                                <div class="tab-pane" id="verify">

                                    <h5 class="info-text"> Enter the OTP sent to the client </h5>
                                    <div class="row justify-content-center">
                                        <div class="col-lg-10">
                                            <div class="row justify-content-center">
                                                <div class="col-sm-8">
                                                    <div class="input-group form-control-lg">
                                                        <div class="form-group">
                                                            <label for="otp" class="bmd-label-floating">OTP (One Time Pin)</label>
                                                            <input type="number" class="form-control" id="otp" name="otp" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="mr-auto">
                                <input type="button" class="btn btn-previous btn-fill btn-default btn-wd disabled" name="previous" value="Previous">
                            </div>
                            <div class="ml-auto">
                                <input type="button" class="btn btn-next btn-fill btn-primary btn-wd" name="next" value="Next">
                                <button type="submit" class="btn btn-finish btn-fill btn-primary btn-wd" style="display: none;">Finish</button>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- wizard container -->
        </div>
        <!-- end row -->
    </div>

@endsection
