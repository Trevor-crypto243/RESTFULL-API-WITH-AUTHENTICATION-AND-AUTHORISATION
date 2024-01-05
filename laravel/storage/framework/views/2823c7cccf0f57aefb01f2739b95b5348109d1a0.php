<?php if(session()->has('warning')): ?>
    <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">&times;</button>
        <strong>Warning!</strong> <?php echo session()->get('warning'); ?>

    </div>
<?php endif; ?><?php /**PATH /home/trevor/Desktop/Beyond/quicksavabackend/laravel/resources/views/layouts/common/warning.blade.php ENDPATH**/ ?>