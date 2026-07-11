{{--
    x-ui.alert-flash
    Renders session flash alerts (success / error / info).
    Drop-in replacement for the repeated @if(session(...)) blocks.
--}}
@foreach(['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $type)
    @if(session($key))
        <x-ui.alert :type="$type">{{ session($key) }}</x-ui.alert>
    @endif
@endforeach
