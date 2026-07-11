{{--
    x-ui.alert
    A single Bootstrap dismissible alert.

    Props:
      $type       – Bootstrap color: success | danger | warning | info | primary | secondary (default: info)
      $id         – Optional HTML id, useful when toggling visibility via JS
      $dismissible – Whether to show the close button (default: true)
      $hidden     – Start with d-none (default: false) — for JS-controlled alerts
      $small      – Apply "small" text size (default: false)
      $mt         – Margin-top utility class suffix, e.g. "2" → "mt-2" (default: none)
      $slot       – Alert message text / HTML

    Static Blade usage (server-side message):
        <x-ui.alert type="success">Saved successfully.</x-ui.alert>
        <x-ui.alert type="danger" :dismissible="false">{{ $errors->first() }}</x-ui.alert>

    JS-controlled placeholder (empty, shown/hidden by JS):
        <x-ui.alert type="danger"  id="import-error"   :hidden="true" :small="true" mt="2" />
        <x-ui.alert type="success" id="import-success" :hidden="true" :small="true" mt="2" />
--}}
@props([
    'type'        => 'info',
    'id'          => null,
    'dismissible' => true,
    'hidden'      => false,
    'small'       => false,
    'mt'          => null,
])

@php
    $classes = implode(' ', array_filter([
        'alert',
        'alert-' . $type,
        $dismissible ? 'alert-dismissible fade show' : '',
        $hidden      ? 'd-none'  : '',
        $small       ? 'small'   : '',
        $mt          ? 'mt-' . $mt : '',
    ]));
@endphp

<div
    @if($id) id="{{ $id }}" @endif
    class="{{ $classes }}"
    role="alert"
>{{ $slot }}@if($dismissible)
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
@endif
</div>
