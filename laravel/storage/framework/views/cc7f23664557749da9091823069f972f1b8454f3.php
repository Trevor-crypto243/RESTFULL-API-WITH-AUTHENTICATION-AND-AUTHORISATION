<?php if(session()->has('success')): ?>
    <div class="alert alert-success">
        <button class="close" data-dismiss="alert">&times;</button>
        <strong>Success!</strong> <?php echo session()->get('success'); ?>

    </div>
<?php endif; ?>
<?php /**PATH /home/trevor/Desktop/Beyond/quicksavabackend/laravel/resources/views/layouts/common/success.blade.php ENDPATH**/ ?>