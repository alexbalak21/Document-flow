{{--
    x-ui.alert-flash
    Renders session flash alerts (success / error / info).
    Drop-in replacement for the repeated @if(session(...)) blocks.
--}}
@foreach(['success' => 'alert-success', 'error' => 'alert-danger', 'info' => 'alert-info'] as $key => $class)
    @if(session($key))
    <div class="alert {{ $class }} alert-dismissible fade show py-2" role="alert">
        {{ session($key) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
@endforeach
