@extends('layouts.app')
@section('title', 'Dashboard')

@push('css')
    <link type="text/css" rel="stylesheet" href="{{url('waitMe/waitMe.css')}}">
@endpush
@push('js')
    <script src="{{url('waitMe/waitMe.js')}}"></script>

    <script>
        var color = '#8e24aa';


        var total_customers = $('#total_customers').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var checkoff_customers = $('#checkoff_customers').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var approved_today = $('#approved_today').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var paid_today = $('#paid_today').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var total_disbursed = $('#total_disbursed').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var total_repaid = $('#total_repaid').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var due_today = $('#due_today').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var overdue = $('#overdue').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var wallets = $('#wallets').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var totalWalletWithdrawalsLoading = $('#total-wallet-withdrawals').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });

        var todaysWalletWithdrawalsLoading = $('#todays-wallet-withdrawals').waitMe({
            effect : 'pulse',
            text : 'Loading...',
            color : color,
            waitTime : -1,
            textPos : 'vertical',
            onClose : function() {}
        });




        function getTotalCustomers() {
            var totalCustomers = $("#totalCustomers");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_total_customers/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        totalCustomers.html(resp.message);
                        total_customers.waitMe('hide');
                    } else {
                        totalCustomers.html(resp.message);
                        total_customers.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        totalCustomers.html("Timeout Error. Please try again");
                    } else {
                        totalCustomers.html(errorThrown);
                    }
                }
            });
        }

        function getCheckoffCustomers() {
            var checkoffCustomers = $("#checkoffCustomers");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_checkoff_customers/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        checkoffCustomers.html(resp.message);
                        checkoff_customers.waitMe('hide');
                    } else {
                        checkoffCustomers.html(resp.message);
                        checkoff_customers.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        checkoffCustomers.html("Timeout Error. Please try again");
                    } else {
                        checkoffCustomers.html(errorThrown);
                    }
                }
            });
        }

        function getApproveToday() {
            var approvedToday = $("#approvedToday");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_approved_today/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        approvedToday.html(resp.message);
                        approved_today.waitMe('hide');
                    } else {
                        approvedToday.html(resp.message);
                        approved_today.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        approvedToday.html("Timeout Error. Please try again");
                    } else {
                        approvedToday.html(errorThrown);
                    }
                }
            });
        }

        function getPaidToday() {
            var paidToday = $("#paidToday");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_paid_today/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        paidToday.html(resp.message);
                        paid_today.waitMe('hide');
                    } else {
                        paidToday.html(resp.message);
                        paid_today.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        paidToday.html("Timeout Error. Please try again");
                    } else {
                        paidToday.html(errorThrown);
                    }
                }
            });
        }

        function getTotalDisbursed() {
            var totalDisbursed = $("#totalDisbursed");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_total_disbursed/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        totalDisbursed.html(resp.message);
                        total_disbursed.waitMe('hide');
                    } else {
                        totalDisbursed.html(resp.message);
                        total_disbursed.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        totalDisbursed.html("Timeout Error. Please try again");
                    } else {
                        totalDisbursed.html(errorThrown);
                    }
                }
            });
        }

        function getTotalRepaid() {
            var totalRepaid = $("#totalRepaid");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_total_repaid/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        totalRepaid.html(resp.message);
                        total_repaid.waitMe('hide');
                    } else {
                        totalRepaid.html(resp.message);
                        total_repaid.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        totalRepaid.html("Timeout Error. Please try again");
                    } else {
                        totalRepaid.html(errorThrown);
                    }
                }
            });
        }

        function getDueToday() {
            var dueToday = $("#dueToday");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_due_today/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        dueToday.html(resp.message);
                        due_today.waitMe('hide');
                    } else {
                        dueToday.html(resp.message);
                        due_today.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        dueToday.html("Timeout Error. Please try again");
                    } else {
                        dueToday.html(errorThrown);
                    }
                }
            });
        }

        function getOverdue() {
            var overdueAmount = $("#overdueAmount");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_overdue/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        overdueAmount.html(resp.message);
                        overdue.waitMe('hide');
                    } else {
                        overdueAmount.html(resp.message);
                        overdue.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        overdueAmount.html("Timeout Error. Please try again");
                    } else {
                        overdueAmount.html(errorThrown);
                    }
                }
            });
        }

        function getWalletsAmount() {
            var walletsAmount = $("#walletsAmount");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_wallets_amount/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        walletsAmount.html(resp.message);
                        wallets.waitMe('hide');
                    } else {
                        walletsAmount.html(resp.message);
                        wallets.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        walletsAmount.html("Timeout Error. Please try again");
                    } else {
                        walletsAmount.html(errorThrown);
                    }
                }
            });
        }

        function getTotalWalletsWithdrawal() {
            var totalWalletWithdrawals = $("#totalWalletWithdrawals");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_total_wallet_withdrawals/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        totalWalletWithdrawals.html(resp.message);
                        totalWalletWithdrawalsLoading.waitMe('hide');
                    } else {
                        totalWalletWithdrawals.html(resp.message);
                        totalWalletWithdrawalsLoading.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        totalWalletWithdrawals.html("Timeout Error. Please try again");
                    } else {
                        totalWalletWithdrawals.html(errorThrown);
                    }
                }
            });
        }

        function getTodaysWalletsWithdrawals() {
            var todaysWalletWithdrawals = $("#todaysWalletWithdrawals");
            $.ajax({
                type: "GET",
                url: "{!! url('/dashboard/get_todays_wallet_withdrawals/') !!}",
                data: {"_token": "{{ csrf_token() }}"},
                //timeout: 5000,
                success: function (resp) {
                    console.log(resp);
                    resp = JSON.parse(resp);
                    if (resp.error) {
                        todaysWalletWithdrawals.html(resp.message);
                        todaysWalletWithdrawalsLoading.waitMe('hide');
                    } else {
                        todaysWalletWithdrawals.html(resp.message);
                        todaysWalletWithdrawalsLoading.waitMe('hide');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        todaysWalletWithdrawals.html("Timeout Error. Please try again");
                    } else {
                        todaysWalletWithdrawals.html(errorThrown);
                    }
                }
            });
        }






        $(document).ready(function(){
            getTotalCustomers();
            getCheckoffCustomers();
            getApproveToday();
            getPaidToday();
            getTotalDisbursed();
            getTotalRepaid();
            getDueToday();
            getOverdue();
            getWalletsAmount();
            getTotalWalletsWithdrawal();
            getTodaysWalletsWithdrawals();

        });

    </script>


    <script type = "text/javascript" src = "https://www.gstatic.com/charts/loader.js"></script>
    <script type = "text/javascript">
        google.charts.load('current', {packages: ['corechart','line']});
    </script>
    <script>
        function drawChart() {
            // Define the chart to be drawn.
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Day');
            data.addColumn('number', 'Approvals');
            data.addColumn('number', 'Payments');
            data.addRows([
                <?php foreach (\Carbon\CarbonPeriod::create(\Carbon\Carbon::now()->subDays(7), \Carbon\Carbon::now())  as $date): ?>
                ['{{$date->format('jS')}}', {{App\LoanRequest::whereDate('created_at', Carbon\Carbon::createFromDate($date))->sum('amount_requested')}}, {{App\LoanRepayment::whereDate('created_at', Carbon\Carbon::createFromDate($date))->sum('amount_repaid')}}],
                <?php endforeach; ?>
            ]);

            // Set chart options
            var options = {'title' : 'Daily loan approvals and payments',
                hAxis: {
                    title: 'Day'
                },
                vAxis: {
                    title: 'Amount'
                },
                legend: {
                    position: 'bottom'
                },
                'height':400,
                pointsVisible: true
            };

            // Instantiate and draw the chart.
            var chart = new google.visualization.LineChart(document.getElementById('container'));
            chart.draw(data, options);
        }
        google.charts.setOnLoadCallback(drawChart);
    </script>
@endpush

@section('content')
<div class="container">

    <div class="row">
        <div class="col-lg-12">
            @include('layouts.common.success')
            @include('layouts.common.warnings')
            @include('layouts.common.warning')
        </div>


        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('customers')}}">
                <div class="card card-stats" id="total_customers" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">supervised_user_circle</i>
                        </div>
                        <p class="card-category">All Users</p>
                        <h3 class="card-title" id="totalCustomers"></h3>
                    </div>
                </div>
            </a>

        </div>
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('customers/checkoff/summary')}}">
                <div class="card card-stats" id="checkoff_customers" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-icon card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">how_to_reg</i>
                        </div>
                        <p class="card-category">Checkoff Customers</p>
                        <h3 class="card-title" id="checkoffCustomers"></h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('/loans/approved_today')}}">
                <div class="card card-stats" id="approved_today" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">done_all</i>
                        </div>
                        <p class="card-category">Loans Approved Today</p>
                        <h3 class="card-title" id="approvedToday"></h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('loans/repaid/today')}}">
                <div class="card card-stats" id="paid_today" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <p class="card-category">Loans Repaid Today</p>
                        <h3 class="card-title" id="paidToday"></h3>
                    </div>
                </div>
            </a>
        </div>


        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
                <div class="card card-stats" id="total_disbursed" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">arrow_circle_up</i>
                        </div>
                        <p class="card-category">Total Loans Disbursed</p>
                        <h3 class="card-title" id="totalDisbursed"></h3>
                    </div>
                </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('loans/repaid')}}">
                <div class="card card-stats" id="total_repaid" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">bolt</i>
                        </div>
                        <p class="card-category"> Total Loans Repaid</p>
                        <h3 class="card-title" id="totalRepaid"></h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('loans/due_today')}}">
                <div class="card card-stats" id="due_today" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-warning card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">notification_important</i>
                        </div>
                        <p class="card-category">Loans Due Today (KES)</p>
                        <h3 class="card-title" id="dueToday"></h3>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('loans/overdue')}}">
                <div class="card card-stats" id="overdue" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-danger card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">warning</i>
                        </div>
                        <p class="card-category">Overdue (KES)</p>
                        <h3 class="card-title" id="overdueAmount"></h3>
                    </div>
                </div>
            </a>
        </div>


        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('wallets')}}">
                <div class="card card-stats" id="wallets" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-danger card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">account_balance_wallet</i>
                        </div>
                        <p class="card-category">Total Wallets &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Balance </p>
                        <h3 class="card-title" id="walletsAmount"></h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('wallets/transactions/all')}}">
                <div class="card card-stats" id="total-wallet-withdrawals" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-danger card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">functions</i>
                        </div>
                        <p class="card-category">Total Wallet Withdrawals</p>
                        <h3 class="card-title" id="totalWalletWithdrawals"></h3>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6" style="padding-right: 0px">
            <a href="{{url('wallets/transactions/today')}}">
                <div class="card card-stats" id="todays-wallet-withdrawals" style="padding-bottom: 20px; margin-bottom: 15px">
                    <div class="card-header card-header-danger card-header-icon">
                        <div class="card-icon" style="margin-right: 0px; padding: 10px">
                            <i class="material-icons">today</i>
                        </div>
                        <p class="card-category">Today's Wallet Withdrawals</p>
                        <h3 class="card-title" id="todaysWalletWithdrawals"></h3>
                    </div>
                </div>
            </a>
        </div>

    </div>







    <div class="row ">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Overdue Loans</strong>
                        </div>

                        <div class="col-md-3">
                            <a href="{{url('loans/overdue')}}" class="btn btn-primary btn-sm">
                                <i class="fa fa-eye"></i> View All Overdue
                            </a>
                        </div>

                        <div class="col-md-4">
                            <strong>B2C Balances (3028315)</strong>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="table-responsive table-sales">
                                <table class="table border">
                                    <tbody>

                                    @foreach($overdueLoans as $key => $overdue)
                                        <tr>
                                            <td>
                                                {{ $key }}
                                            </td>

                                            <td>
                                                KES {{number_format($overdue)}}
                                            </td>

                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6 " id="b2c" >

                            <div class="col-lg-12 col-md-12 col-sm-12" >
                                <div class="card card-stats"  style="margin-bottom: 20px">
                                    <div class="card-header card-header-primary card-header-icon">
                                        <div class="card-icon" style="padding: 0px; margin-right: 0px">
                                            <i class="material-icons">account_balance</i>
                                        </div>
                                        <p class="card-category">B2C Utility Balance (KES)</p>
                                        <h3 class="card-title" id="b2cUtility">{{number_format(optional(\App\PaybillBalance::where('shortcode','3028315')->first())->utility, 2)}}</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="card card-stats" style="margin-bottom: 20px">
                                    <div class="card-header card-header-warning card-header-icon">
                                        <div class="card-icon" style="padding: 0px; margin-right: 0px">
                                            <i class="material-icons">autorenew</i>
                                        </div>
                                        <p class="card-category">B2C MMF Balance (KES)</p>
                                        <h3 class="card-title" id="b2cMmf">{{number_format(optional(\App\PaybillBalance::where('shortcode','3028315')->first())->mmf, 2)}}</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="card card-stats" style="margin-bottom: 20px">
                                    <div class="card-header card-header-info card-header-icon">
                                        <div class="card-icon" style="padding: 0px; margin-right: 0px">
                                            <i class="material-icons">file_download_done</i>
                                        </div>
                                        <p class="card-category">Total Wallet Deposits (KES)</p>
                                        <h3 class="card-title" id="b2cMmf">{{number_format(\App\MpesaPayment::sum('amount'), 2)}}</h3>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                </div>
            </div>
        </div>

        <div class="col-md-4" style="padding-right: 0px">
            <div class="card" style="margin-top: 0px">

                <div class="card-header">
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Top Wallets</strong>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive table-sales">
                                <table class="table">
                                    <tbody>

                                    @foreach($topPersonal as $personal)
                                        <tr>
                                            <td>
                                                {{$personal->name}}
                                            </td>

                                            <td>
                                                KES {{number_format($personal->total)}}
                                            </td>

                                            <td>
                                                {!!
                                                        $personal->active == 1 ?
                                                        '<span class="badge pill badge-success">ACTIVE</span>':
                                                        '<span class="badge pill badge-warning">FROZEN</span>'
                                                !!}
                                            </td>

                                            <td class="text-right">
                                                <a href="{{url('wallet/customer' ,  $personal->id)}}"
                                                   class="btn btn-primary btn-link btn-sm">
                                                    <i class="material-icons">visibility</i> View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-4" style="padding-right: 0px">
            <div class="card" style="margin-top: 0px">

                <div class="card-header">
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Top Frozen Wallets</strong>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive table-sales">
                                <table class="table">
                                    <tbody>

                                    @foreach($topPersonalFrozen as $personal)
                                        <tr>
                                            <td>
                                                {{$personal->name}}
                                            </td>

                                            <td>
                                                KES {{number_format($personal->total)}}
                                            </td>

                                            <td>
                                                {!!
                                                        $personal->active == 1 ?
                                                        '<span class="badge pill badge-success">ACTIVE</span>':
                                                        '<span class="badge pill badge-warning">FROZEN</span>'
                                                !!}
                                            </td>

                                            <td class="text-right">
                                                <a href="{{url('wallet/customer' ,  $personal->id)}}"
                                                   class="btn btn-primary btn-link btn-sm">
                                                    <i class="material-icons">visibility</i> View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <div class="col-md-12">

            <div class="card card-chart">
                <div class="card-header ">
                    <div id = "container"></div>

                </div>

                    <div class="card-body">

                    <h4 class="card-title">Past week </h4>
                    <p class="card-category">Showing data for the last 7 days</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
