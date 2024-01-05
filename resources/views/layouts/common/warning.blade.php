@if (session()->has('warning'))
    <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">&times;</button>
        <strong>Warning!</strong> {!! session()->get('warning') !!}
    </div>
@endif