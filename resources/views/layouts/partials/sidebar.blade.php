<div class="sidebar" data-color="{{ $btn_color }}" data-background-color="{{ $color }}" data-image="{{ $sidebar_image }}">
    <!--
      Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

      Tip 2: you can also add an image using data-image tag
  -->
    <div class="logo">
{{--        <a href="{{url('/')}}" class="simple-text logo-mini">--}}

{{--            --}}
{{--        </a>--}}
        <a href="{{url('/')}}" class="simple-text logo-normal" style="margin-left: 20px">

            Quicksava

        </a>
    </div>
    <div class="sidebar-wrapper">
        <div class="user">
            <div class="photo">
                <img src="{{  $avatar }}" />
            </div>
            <div class="user-info">
                <a data-toggle="collapse" href="#collapseExample" class="username">
                <span>
                    <small>{{ auth()->user()->name }}</small>
                    <b class="caret"></b>
                </span>
                </a>
                <a class=" col-8 offset-2">
                    <span class="badge badge-secondary" style="font-size:9px;">{{auth()->user()->role->name}}</span>
                </a>

{{--                <div class="collapse" id="collapseExample">--}}
{{--                    <ul class="nav">--}}
{{--                        <li class="nav-item ">--}}
{{--                            <a class="nav-link" href="#" data-toggle="modal" data-target="#org-modal">--}}
{{--                                <i class="fa fa-plus"></i>--}}
{{--                                <span class="sidebar-normal"> Add Organisation </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}

{{--                            <li class="nav-item {{ ('edit-profile' == $current_route->uri) ? 'active child' : '' }}">--}}
{{--                                <a class="nav-link" href="{{ url('organisation/select') }}">--}}
{{--                                    <i class="fa fa-briefcase"></i>--}}
{{--                                    <span class="sidebar-normal"> MENU HERE </span>--}}
{{--                                </a>--}}
{{--                            </li>--}}
{{--                    </ul>--}}
{{--                </div>--}}

            </div>
        </div>
        <ul class="nav">

            <li class="nav-item {{ ('/' == $current_route->uri) ? 'active' : '' }} ">
                <a class="nav-link" href="{{url('/')}}">
                    <i class="material-icons">dashboard</i>
                    <p> Dashboard </p>
                </a>
            </li>



            @if(auth()->user()->user_group == 2)
                {{--HR user--}}

                @if(optional(optional(\App\HrManager::where('user_id', auth()->user()->id)->first())->employer)->salary_advance == true)

                    <li class="nav-item {{\Request::is('hr/employees*') ? 'active' : '' }} ">
                        <a class="nav-link" href="{{url('hr/employees/all')}}">
                            <i class="material-icons">groups</i>
                            <p> Employees </p>
                        </a>
                    </li>


                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#hrInua">
                            <i class="material-icons">fact_check</i>
                            <p> Salary Advance
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="hrInua">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('hr/advance*') ? 'active child' : '' }} ">

                                </li>

                                <li class="nav-item {{ ('hr/advance/pending' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('hr/advance/pending')}}">
                                        <span class="sidebar-mini"> PR </span>
                                        <span class="sidebar-normal"> Pending Requests </span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('hr/advance/approved' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('hr/advance/approved')}}">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal">Approved Requests</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('hr/advance/amendment' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('hr/advance/amendment')}}">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal">Amendment Requests</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('hr/advance/rejected' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('hr/advance/rejected')}}">
                                        <span class="sidebar-mini"> RR </span>
                                        <span class="sidebar-normal">Rejected Requests</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                @endif



            @elseif(auth()->user()->user_group == 5)
{{--                Merchant Accounts Manager--}}
                @if(optional(optional(\App\HrManager::where('user_id', auth()->user()->id)->first())->employer)->invoice_discounting == true)

                    <li class="nav-item {{ \Request::is('manager/invoices*') ? 'active' : '' }} ">
                        <a class="nav-link" href="{{url('manager/invoices')}}">
                            <i class="material-icons">receipt</i>
                            <p> Invoices </p>
                        </a>
                    </li>
                @endif


            @else
{{--                other users. admin, etc--}}
                @if(auth()->user()->role->has_perm([3,43]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#customers">
                            <i class="material-icons">supervisor_account</i>
                            <p> Customers
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="customers">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('customers*') ? 'active child' : '' }} ">

                                </li>

                                @if(auth()->user()->role->has_perm([3]))
                                    <li class="nav-item {{ ('customers' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('customers')}}">
                                            <span class="sidebar-mini"> AC </span>
                                            <span class="sidebar-normal"> All Customers </span>
                                        </a>
                                    </li>

                                    <li class="nav-item {{ ('customers/checkoff' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('customers/checkoff')}}">
                                            <span class="sidebar-mini"> CC </span>
                                            <span class="sidebar-normal"> Checkoff Customers </span>
                                        </a>
                                    </li>
                                @endif

                                @if(auth()->user()->role->has_perm([43]))
                                    <li class="nav-item {{ ('customers/managed' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('customers/managed')}}">
                                            <span class="sidebar-mini"> MC </span>
                                            <span class="sidebar-normal"> Managed Customers </span>
                                        </a>
                                    </li>
                                @endif

                                @if(auth()->user()->role->has_perm([3]))
                                    <li class="nav-item {{ ('customers/new-leads' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('customers/new-leads')}}">
                                            <span class="sidebar-mini"> NL </span>
                                            <span class="sidebar-normal"> New Leads </span>
                                        </a>
                                    </li>
                                @endif


                            </ul>
                        </div>
                    </li>

                @endif

                @if(auth()->user()->role->has_perm([4]))
                    <li class="nav-item {{ ('bulk/messaging' == $current_route->uri) ? 'active' : '' }} ">
                        <a class="nav-link" href="{{url('/bulk/messaging')}}">
                            <i class="material-icons">chat</i>
                            <p> Messaging </p>
                        </a>
                    </li>
                @endif


                @if(auth()->user()->role->has_perm([11]))
                    <li class="nav-item  {{ \Request::is('partners*') ? 'active' : '' }} ">
                        <a class="nav-link" href="{{url('partners')}}">
                            <i class="material-icons">work</i>
                            <p> Partners/Employers </p>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->role->has_perm([9]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#employees">
                            <i class="material-icons">groups</i>
                            <p> Salary Advance
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="employees">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('advance*') ? 'active child' : '' }} ">

                                </li>
                                <li class="nav-item {{ ('advance/requests/new' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('advance/requests/new')}}">
                                        <span class="sidebar-mini"> NR </span>
                                        <span class="sidebar-normal"> New Requests </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ ('advance/requests/progressing' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('advance/requests/progressing')}}">
                                        <span class="sidebar-mini"> IP </span>
                                        <span class="sidebar-normal"> In Progress </span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('advance/requests/amending' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('advance/requests/amending')}}">
                                        <span class="sidebar-mini"> IA </span>
                                        <span class="sidebar-normal"> In Amendment </span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('advance/requests/accepted' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('advance/requests/accepted')}}">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal"> Accepted Requests </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ ('advance/requests/rejected' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('advance/requests/rejected')}}">
                                        <span class="sidebar-mini"> RR </span>
                                        <span class="sidebar-normal"> Rejected Requests </span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                @endif

                @if(auth()->user()->role->has_perm([26]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#auto">
                            <i class="material-icons">directions_car</i>
                            <p> Auto Logbook
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="auto">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('auto*') ? 'active child' : '' }} ">

                                </li>
                                @if(auth()->user()->role->has_perm([26]))
                                    <li class="nav-item {{ ('auto/models' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('auto/models')}}">
                                            <span class="sidebar-mini"> VM </span>
                                            <span class="sidebar-normal"> Makes/Models </span>
                                        </a>
                                    </li>
                                @endif
                                @if(auth()->user()->role->has_perm([28]))
                                    <li class="nav-item {{ ('auto/applications' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('auto/applications')}}">
                                            <span class="sidebar-mini"> LA </span>
                                            <span class="sidebar-normal"> Logbook Applications </span>
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    </li>
                @endif

                @if(auth()->user()->role->has_perm([13]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#loans">
                            <i class="material-icons">local_atm</i>
                            <p> All Loans
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="loans">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('loans*') ? 'active child' : '' }} ">

                                </li>
                                <li class="nav-item {{ ('loans/all' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('loans/all')}}">
                                        <span class="sidebar-mini"> AL </span>
                                        <span class="sidebar-normal"> All Loans </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ ('loans/due_today' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('loans/due_today')}}">
                                        <span class="sidebar-mini"> DT </span>
                                        <span class="sidebar-normal"> Loans Due Today </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ ('loans/repaid' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('loans/repaid')}}">
                                        <span class="sidebar-mini"> RL </span>
                                        <span class="sidebar-normal"> All Repaid Loans </span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('loans/repaid/today' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('loans/repaid/today')}}">
                                        <span class="sidebar-mini"> RT </span>
                                        <span class="sidebar-normal"> Loans Repaid Today </span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('loans/approved_today' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('loans/approved_today')}}">
                                        <span class="sidebar-mini"> AT </span>
                                        <span class="sidebar-normal"> Loans Approved Today </span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('loans/overdue' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('loans/overdue')}}">
                                        <span class="sidebar-mini"> OL </span>
                                        <span class="sidebar-normal"> Overdue Loans </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                @if(auth()->user()->role->has_perm([16,17,18]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#recon">
                            <i class="material-icons">restore</i>
                            <p> Reconciliations
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="recon">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('recon*') ? 'active child' : '' }} ">

                                </li>
                                @if(auth()->user()->role->has_perm([16]))
                                    <li class="nav-item {{ ('recon/suspense' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('recon/suspense')}}">
                                            <span class="sidebar-mini"> &nbsp; </span>
                                            <span class="sidebar-normal"> Suspense </span>
                                        </a>
                                    </li>
                                @endif

                                @if(auth()->user()->role->has_perm([17]))
                                    <li class="nav-item {{ ('recon/c2b' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('recon/c2b')}}">
                                            <span class="sidebar-mini"> &nbsp; </span>
                                            <span class="sidebar-normal"> C2B </span>
                                        </a>
                                    </li>
                                @endif

                                @if(auth()->user()->role->has_perm([51]))
                                    <li class="nav-item {{ ('recon/b2c' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('recon/b2c')}}">
                                            <span class="sidebar-mini"> &nbsp; </span>
                                            <span class="sidebar-normal"> B2C </span>
                                        </a>
                                    </li>
                                @endif

                                {{--                                @if(auth()->user()->role->has_perm([18]))--}}
                                {{--                                    <li class="nav-item {{ ('recon/bulk_disburse' == $current_route->uri) ? 'active child' : '' }} ">--}}
                                {{--                                        <a class="nav-link" href="{{url('recon/bulk_disburse')}}">--}}
                                {{--                                            <span class="sidebar-mini"> &nbsp; </span>--}}
                                {{--                                            <span class="sidebar-normal"> Bulk Disburse </span>--}}
                                {{--                                        </a>--}}
                                {{--                                    </li>--}}
                                {{--                                @endif--}}
                            </ul>
                        </div>
                    </li>
                @endif

                @if(auth()->user()->role->has_perm([6]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#reports">
                            <i class="material-icons">difference</i>
                            <p> Reports
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="reports">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('reports*') ? 'active child' : '' }} ">

                                </li>

                                <li class="nav-item {{ ('reports/advance_repayments' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('reports/advance_repayments')}}">
                                        <span class="sidebar-mini"> RD </span>
                                        <span class="sidebar-normal">Repayments Due</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('reports/repayments' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('reports/repayments')}}">
                                        <span class="sidebar-mini"> RP </span>
                                        <span class="sidebar-normal">Repayments Paid</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('reports/insurance_data' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('reports/insurance_data')}}">
                                        <span class="sidebar-mini"> ID </span>
                                        <span class="sidebar-normal">Insurance Data</span>
                                    </a>
                                </li>


                                <li class="nav-item {{ ('reports/running_lb' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('reports/running_lb')}}">
                                        <span class="sidebar-mini"> RB </span>
                                        <span class="sidebar-normal">Running Loan Balance</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('reports/mtd' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('reports/mtd')}}">
                                        <span class="sidebar-mini"> MT </span>
                                        <span class="sidebar-normal">MTD - Monthly</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('reports/mtd/range' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('reports/mtd/range')}}">
                                        <span class="sidebar-mini"> MT </span>
                                        <span class="sidebar-normal">MTD - Date Range</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ ('reports/ageing' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('reports/ageing')}}">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal">Ageing Report</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                @endif


                @if(auth()->user()->role->has_perm([14,15]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#prods">
                            <i class="material-icons">settings</i>
                            <p> Product Configs
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="prods">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('products*') ? 'active child' : '' }} ">

                                </li>
                                @if(auth()->user()->role->has_perm([14]))
                                    <li class="nav-item {{ ('products/loans' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('products/loans')}}">
                                            <span class="sidebar-mini"> LP </span>
                                            <span class="sidebar-normal"> Loan Products </span>
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    </li>
                @endif


                @if(auth()->user()->role->has_perm([47,48,49,50]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#banks">
                            <i class="material-icons">apartment</i>
                            <p> Banks
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="banks">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('bank*') ? 'active child' : '' }} ">

                                </li>
                                @if(auth()->user()->role->has_perm([47]))
                                    <li class="nav-item {{ ('bank/banks' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('bank/banks')}}">
                                            <span class="sidebar-mini"> B </span>
                                            <span class="sidebar-normal"> Banks </span>
                                        </a>
                                    </li>
                                @endif
                                @if(auth()->user()->role->has_perm([49]))
                                    <li class="nav-item {{ ('bank/accounts' == $current_route->uri) ? 'active child' : '' }} ">
                                        <a class="nav-link" href="{{url('bank/accounts')}}">
                                            <span class="sidebar-mini"> BA </span>
                                            <span class="sidebar-normal"> Bank Accounts </span>
                                        </a>
                                    </li>
                                @endif

                            </ul>
                        </div>
                    </li>
                @endif

                @if(auth()->user()->role->has_perm([1,2]))
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#um">
                            <i class="material-icons">admin_panel_settings</i>
                            <p> User Management
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="um">
                            <ul class="nav">
                                <li class="nav-item {{ \Request::is('user*') ? 'active child' : '' }} ">

                                </li>

                                @if(auth()->user()->role->has_perm([1]))
                                <li class="nav-item {{ ('user_groups' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('user_groups')}}">
                                        <span class="sidebar-mini"> UG </span>
                                        <span class="sidebar-normal"> User Groups </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ ('users' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('users')}}">
                                        <span class="sidebar-mini"> AU </span>
                                        <span class="sidebar-normal"> All Users </span>
                                    </a>
                                </li>
                                @endif

                                @if(auth()->user()->role->has_perm([2]))
                                <li class="nav-item {{ ('audit_logs' == $current_route->uri) ? 'active child' : '' }} ">
                                    <a class="nav-link" href="{{url('audit_logs')}}">
                                        <span class="sidebar-mini"> AL </span>
                                        <span class="sidebar-normal"> Audit Logs </span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

            @endif






            {{--            @if(auth()->user()->role->has_perm([1]))--}}

{{--            <li class="nav-item ">--}}
{{--                <a class="nav-link " data-toggle="collapse"  href="#bw">--}}
{{--                    <i class="material-icons">lock</i>--}}
{{--                    <p> Blacklist/Whitelist--}}
{{--                        <b class="caret"></b>--}}
{{--                    </p>--}}
{{--                </a>--}}
{{--                <div class="collapse" id="bw">--}}
{{--                    <ul class="nav">--}}
{{--                        <li class="nav-item {{ \Request::is('blacklist*') || \Request::is('whitelist*') ? 'active child' : '' }} ">--}}

{{--                        </li>--}}
{{--                        <li class="nav-item {{ ('blacklist' == $current_route->uri) ? 'active child' : '' }} ">--}}
{{--                            <a class="nav-link" href="{{url('blacklist')}}">--}}
{{--                                <span class="sidebar-mini"> &nbsp; </span>--}}
{{--                                <span class="sidebar-normal"> Blacklist Users </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li class="nav-item {{ ('whitelist' == $current_route->uri) ? 'active child' : '' }} ">--}}
{{--                            <a class="nav-link" href="{{url('whitelist')}}">--}}
{{--                                <span class="sidebar-mini"> &nbsp; </span>--}}
{{--                                <span class="sidebar-normal"> Whitelist Users </span>--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </div>--}}
{{--            </li>--}}

            {{--            @endif--}}


        </ul>





    </div>
</div>

@push('js')
    <script>
        $('li.child.active:first').parents('li').addClass('active');
        $('li.child.active:first').parents('div.collapse').addClass('show');

        $('.portal-logout').on('click', function() {
            $('#portal-logout-form').submit();
        });

        $(function() {
            $('#avatar-img').on('click', function() {
                location.href = '{{ url('/') }}'
            });
        });
    </script>
@endpush
