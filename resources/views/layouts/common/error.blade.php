@if(session()->has('error'))
    <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">&times;</button>
        <strong>Warning!</strong> {!! session()->get('error') !!}
    </div>
@endif