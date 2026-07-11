{{--
    x-ui.empty-state
    Props:
      $message – text to display
--}}
@props(['message'])
<div class="card border-0 shadow-sm">
    <div class="card-body text-center text-muted py-5">
        {{ $message }}
        {{ $slot }}
    </div>
</div>
