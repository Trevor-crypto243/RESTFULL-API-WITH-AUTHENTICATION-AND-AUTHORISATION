@if(session()->has('info'))
    <div class="alert alert-info">
        <button class="close" data-dismiss="alert">&times;</button>
        <strong>Info!</strong> {!! session()->get('info') !!}
    </div>
@endif