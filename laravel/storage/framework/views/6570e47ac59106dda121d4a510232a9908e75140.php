<div class="sidebar" data-color="<?php echo e($btn_color); ?>" data-background-color="<?php echo e($color); ?>" data-image="<?php echo e($sidebar_image); ?>">
    <!--
      Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

      Tip 2: you can also add an image using data-image tag
  -->
    <div class="logo">




        <a href="<?php echo e(url('/')); ?>" class="simple-text logo-normal" style="margin-left: 20px">

            Quicksava

        </a>
    </div>
    <div class="sidebar-wrapper">
        <div class="user">
            <div class="photo">
                <img src="<?php echo e($avatar); ?>" />
            </div>
            <div class="user-info">
                <a data-toggle="collapse" href="#collapseExample" class="username">
                <span>
                    <small><?php echo e(auth()->user()->name); ?></small>
                    <b class="caret"></b>
                </span>
                </a>
                <a class=" col-8 offset-2">
                    <span class="badge badge-secondary" style="font-size:9px;"><?php echo e(auth()->user()->role->name); ?></span>
                </a>



















            </div>
        </div>
        <ul class="nav">

            <li class="nav-item <?php echo e(('/' == $current_route->uri) ? 'active' : ''); ?> ">
                <a class="nav-link" href="<?php echo e(url('/')); ?>">
                    <i class="material-icons">dashboard</i>
                    <p> Dashboard </p>
                </a>
            </li>



            <?php if(auth()->user()->user_group == 2): ?>
                

                <?php if(optional(optional(\App\HrManager::where('user_id', auth()->user()->id)->first())->employer)->salary_advance == true): ?>

                    <li class="nav-item <?php echo e(\Request::is('hr/employees*') ? 'active' : ''); ?> ">
                        <a class="nav-link" href="<?php echo e(url('hr/employees/all')); ?>">
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
                                <li class="nav-item <?php echo e(\Request::is('hr/advance*') ? 'active child' : ''); ?> ">

                                </li>

                                <li class="nav-item <?php echo e(('hr/advance/pending' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('hr/advance/pending')); ?>">
                                        <span class="sidebar-mini"> PR </span>
                                        <span class="sidebar-normal"> Pending Requests </span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('hr/advance/approved' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('hr/advance/approved')); ?>">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal">Approved Requests</span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('hr/advance/amendment' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('hr/advance/amendment')); ?>">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal">Amendment Requests</span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('hr/advance/rejected' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('hr/advance/rejected')); ?>">
                                        <span class="sidebar-mini"> RR </span>
                                        <span class="sidebar-normal">Rejected Requests</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                <?php endif; ?>



            <?php elseif(auth()->user()->user_group == 5): ?>

                <?php if(optional(optional(\App\HrManager::where('user_id', auth()->user()->id)->first())->employer)->invoice_discounting == true): ?>

                    <li class="nav-item <?php echo e(\Request::is('manager/invoices*') ? 'active' : ''); ?> ">
                        <a class="nav-link" href="<?php echo e(url('manager/invoices')); ?>">
                            <i class="material-icons">receipt</i>
                            <p> Invoices </p>
                        </a>
                    </li>
                <?php endif; ?>


            <?php else: ?>

                <?php if(auth()->user()->role->has_perm([3,43])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#customers">
                            <i class="material-icons">supervisor_account</i>
                            <p> Customers
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="customers">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('customers*') ? 'active child' : ''); ?> ">

                                </li>

                                <?php if(auth()->user()->role->has_perm([3])): ?>
                                    <li class="nav-item <?php echo e(('customers' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('customers')); ?>">
                                            <span class="sidebar-mini"> AC </span>
                                            <span class="sidebar-normal"> All Customers </span>
                                        </a>
                                    </li>

                                    <li class="nav-item <?php echo e(('customers/checkoff' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('customers/checkoff')); ?>">
                                            <span class="sidebar-mini"> CC </span>
                                            <span class="sidebar-normal"> Checkoff Customers </span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if(auth()->user()->role->has_perm([43])): ?>
                                    <li class="nav-item <?php echo e(('customers/managed' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('customers/managed')); ?>">
                                            <span class="sidebar-mini"> MC </span>
                                            <span class="sidebar-normal"> Managed Customers </span>
                                        </a>
                                    </li>
                                <?php endif; ?>


                            </ul>
                        </div>
                    </li>

                <?php endif; ?>

                <?php if(auth()->user()->role->has_perm([4])): ?>
                    <li class="nav-item <?php echo e(('bulk/messaging' == $current_route->uri) ? 'active' : ''); ?> ">
                        <a class="nav-link" href="<?php echo e(url('/bulk/messaging')); ?>">
                            <i class="material-icons">chat</i>
                            <p> Messaging </p>
                        </a>
                    </li>
                <?php endif; ?>


                <?php if(auth()->user()->role->has_perm([11])): ?>
                    <li class="nav-item  <?php echo e(\Request::is('partners*') ? 'active' : ''); ?> ">
                        <a class="nav-link" href="<?php echo e(url('partners')); ?>">
                            <i class="material-icons">work</i>
                            <p> Partners/Employers </p>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->has_perm([9])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#employees">
                            <i class="material-icons">groups</i>
                            <p> Salary Advance
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="employees">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('advance*') ? 'active child' : ''); ?> ">

                                </li>
                                <li class="nav-item <?php echo e(('advance/requests/new' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('advance/requests/new')); ?>">
                                        <span class="sidebar-mini"> NR </span>
                                        <span class="sidebar-normal"> New Requests </span>
                                    </a>
                                </li>
                                <li class="nav-item <?php echo e(('advance/requests/progressing' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('advance/requests/progressing')); ?>">
                                        <span class="sidebar-mini"> IP </span>
                                        <span class="sidebar-normal"> In Progress </span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('advance/requests/amending' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('advance/requests/amending')); ?>">
                                        <span class="sidebar-mini"> IA </span>
                                        <span class="sidebar-normal"> In Amendment </span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('advance/requests/accepted' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('advance/requests/accepted')); ?>">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal"> Accepted Requests </span>
                                    </a>
                                </li>
                                <li class="nav-item <?php echo e(('advance/requests/rejected' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('advance/requests/rejected')); ?>">
                                        <span class="sidebar-mini"> RR </span>
                                        <span class="sidebar-normal"> Rejected Requests </span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->has_perm([26])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#auto">
                            <i class="material-icons">directions_car</i>
                            <p> Auto Logbook
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="auto">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('auto*') ? 'active child' : ''); ?> ">

                                </li>
                                <?php if(auth()->user()->role->has_perm([26])): ?>
                                    <li class="nav-item <?php echo e(('auto/models' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('auto/models')); ?>">
                                            <span class="sidebar-mini"> VM </span>
                                            <span class="sidebar-normal"> Makes/Models </span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if(auth()->user()->role->has_perm([28])): ?>
                                    <li class="nav-item <?php echo e(('auto/applications' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('auto/applications')); ?>">
                                            <span class="sidebar-mini"> LA </span>
                                            <span class="sidebar-normal"> Logbook Applications </span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->has_perm([13])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#loans">
                            <i class="material-icons">local_atm</i>
                            <p> All Loans
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="loans">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('loans*') ? 'active child' : ''); ?> ">

                                </li>
                                <li class="nav-item <?php echo e(('loans/all' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('loans/all')); ?>">
                                        <span class="sidebar-mini"> AL </span>
                                        <span class="sidebar-normal"> All Loans </span>
                                    </a>
                                </li>
                                <li class="nav-item <?php echo e(('loans/due_today' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('loans/due_today')); ?>">
                                        <span class="sidebar-mini"> DT </span>
                                        <span class="sidebar-normal"> Loans Due Today </span>
                                    </a>
                                </li>
                                <li class="nav-item <?php echo e(('loans/repaid' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('loans/repaid')); ?>">
                                        <span class="sidebar-mini"> RL </span>
                                        <span class="sidebar-normal"> All Repaid Loans </span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('loans/repaid/today' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('loans/repaid/today')); ?>">
                                        <span class="sidebar-mini"> RT </span>
                                        <span class="sidebar-normal"> Loans Repaid Today </span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('loans/approved_today' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('loans/approved_today')); ?>">
                                        <span class="sidebar-mini"> AT </span>
                                        <span class="sidebar-normal"> Loans Approved Today </span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('loans/overdue' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('loans/overdue')); ?>">
                                        <span class="sidebar-mini"> OL </span>
                                        <span class="sidebar-normal"> Overdue Loans </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->has_perm([16,17,18])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#recon">
                            <i class="material-icons">restore</i>
                            <p> Reconciliations
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="recon">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('recon*') ? 'active child' : ''); ?> ">

                                </li>
                                <?php if(auth()->user()->role->has_perm([16])): ?>
                                    <li class="nav-item <?php echo e(('recon/suspense' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('recon/suspense')); ?>">
                                            <span class="sidebar-mini"> &nbsp; </span>
                                            <span class="sidebar-normal"> Suspense </span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if(auth()->user()->role->has_perm([17])): ?>
                                    <li class="nav-item <?php echo e(('recon/c2b' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('recon/c2b')); ?>">
                                            <span class="sidebar-mini"> &nbsp; </span>
                                            <span class="sidebar-normal"> C2B </span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if(auth()->user()->role->has_perm([51])): ?>
                                    <li class="nav-item <?php echo e(('recon/b2c' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('recon/b2c')); ?>">
                                            <span class="sidebar-mini"> &nbsp; </span>
                                            <span class="sidebar-normal"> B2C </span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                
                                
                                
                                
                                
                                
                                
                                
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->has_perm([6])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#reports">
                            <i class="material-icons">difference</i>
                            <p> Reports
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="reports">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('reports*') ? 'active child' : ''); ?> ">

                                </li>

                                <li class="nav-item <?php echo e(('reports/advance_repayments' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('reports/advance_repayments')); ?>">
                                        <span class="sidebar-mini"> RD </span>
                                        <span class="sidebar-normal">Repayments Due</span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('reports/repayments' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('reports/repayments')); ?>">
                                        <span class="sidebar-mini"> RP </span>
                                        <span class="sidebar-normal">Repayments Paid</span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('reports/insurance_data' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('reports/insurance_data')); ?>">
                                        <span class="sidebar-mini"> ID </span>
                                        <span class="sidebar-normal">Insurance Data</span>
                                    </a>
                                </li>


                                <li class="nav-item <?php echo e(('reports/running_lb' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('reports/running_lb')); ?>">
                                        <span class="sidebar-mini"> RB </span>
                                        <span class="sidebar-normal">Running Loan Balance</span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('reports/mtd' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('reports/mtd')); ?>">
                                        <span class="sidebar-mini"> MT </span>
                                        <span class="sidebar-normal">MTD - Monthly</span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('reports/mtd/range' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('reports/mtd/range')); ?>">
                                        <span class="sidebar-mini"> MT </span>
                                        <span class="sidebar-normal">MTD - Date Range</span>
                                    </a>
                                </li>

                                <li class="nav-item <?php echo e(('reports/ageing' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('reports/ageing')); ?>">
                                        <span class="sidebar-mini"> AR </span>
                                        <span class="sidebar-normal">Ageing Report</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>
                <?php endif; ?>


                <?php if(auth()->user()->role->has_perm([14,15])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#prods">
                            <i class="material-icons">settings</i>
                            <p> Product Configs
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="prods">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('products*') ? 'active child' : ''); ?> ">

                                </li>
                                <?php if(auth()->user()->role->has_perm([14])): ?>
                                    <li class="nav-item <?php echo e(('products/loans' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('products/loans')); ?>">
                                            <span class="sidebar-mini"> LP </span>
                                            <span class="sidebar-normal"> Loan Products </span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </div>
                    </li>
                <?php endif; ?>


                <?php if(auth()->user()->role->has_perm([47,48,49,50])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#banks">
                            <i class="material-icons">apartment</i>
                            <p> Banks
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="banks">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('bank*') ? 'active child' : ''); ?> ">

                                </li>
                                <?php if(auth()->user()->role->has_perm([47])): ?>
                                    <li class="nav-item <?php echo e(('bank/banks' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('bank/banks')); ?>">
                                            <span class="sidebar-mini"> B </span>
                                            <span class="sidebar-normal"> Banks </span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if(auth()->user()->role->has_perm([49])): ?>
                                    <li class="nav-item <?php echo e(('bank/accounts' == $current_route->uri) ? 'active child' : ''); ?> ">
                                        <a class="nav-link" href="<?php echo e(url('bank/accounts')); ?>">
                                            <span class="sidebar-mini"> BA </span>
                                            <span class="sidebar-normal"> Bank Accounts </span>
                                        </a>
                                    </li>
                                <?php endif; ?>

                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if(auth()->user()->role->has_perm([1,2])): ?>
                    <li class="nav-item ">
                        <a class="nav-link " data-toggle="collapse"  href="#um">
                            <i class="material-icons">admin_panel_settings</i>
                            <p> User Management
                                <b class="caret"></b>
                            </p>
                        </a>
                        <div class="collapse" id="um">
                            <ul class="nav">
                                <li class="nav-item <?php echo e(\Request::is('user*') ? 'active child' : ''); ?> ">

                                </li>

                                <?php if(auth()->user()->role->has_perm([1])): ?>
                                <li class="nav-item <?php echo e(('user_groups' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('user_groups')); ?>">
                                        <span class="sidebar-mini"> UG </span>
                                        <span class="sidebar-normal"> User Groups </span>
                                    </a>
                                </li>
                                <li class="nav-item <?php echo e(('users' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('users')); ?>">
                                        <span class="sidebar-mini"> AU </span>
                                        <span class="sidebar-normal"> All Users </span>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <?php if(auth()->user()->role->has_perm([2])): ?>
                                <li class="nav-item <?php echo e(('audit_logs' == $current_route->uri) ? 'active child' : ''); ?> ">
                                    <a class="nav-link" href="<?php echo e(url('audit_logs')); ?>">
                                        <span class="sidebar-mini"> AL </span>
                                        <span class="sidebar-normal"> Audit Logs </span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

            <?php endif; ?>






            





























            


        </ul>





    </div>
</div>

<?php $__env->startPush('js'); ?>
    <script>
        $('li.child.active:first').parents('li').addClass('active');
        $('li.child.active:first').parents('div.collapse').addClass('show');

        $('.portal-logout').on('click', function() {
            $('#portal-logout-form').submit();
        });

        $(function() {
            $('#avatar-img').on('click', function() {
                location.href = '<?php echo e(url('/')); ?>'
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/trevor/Desktop/Beyond/quicksavabackend/laravel/resources/views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>