<?php if(count($errors->all())): ?>
    <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">&times;</button>
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?><?php /**PATH /home/trevor/Desktop/Beyond/quicksavabackend/laravel/resources/views/layouts/common/warnings.blade.php ENDPATH**/ ?>