<?php $__env->startSection('title', '403 - Access Denied!'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="card">
                <div class="card-header card-header-primary card-header-icon">
                    <div class="card-icon">
                        <i class="material-icons">warning</i>
                    </div>
                    
                </div>
                <div class="card-body text-center">
                    <h1>403 - Access Denied!</h1>
                    <h5><?php echo e($exception->getMessage()); ?></h5>
                    <a href="<?php echo e(url('/')); ?>" class="btn btn-primary">Home</a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/trevor/Desktop/Beyond/quicksavabackend/laravel/resources/views/errors/403.blade.php ENDPATH**/ ?>