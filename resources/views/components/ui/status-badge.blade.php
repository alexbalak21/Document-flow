{{--
    x-ui.status-badge
    Props:
      $status – document status string (draft, sent, paid, etc.)
--}}
@props(['status'])
@php $color = \App\Models\Document::$statusColors[$status] ?? 'secondary'; @endphp
<span class="badge text-bg-{{ $color }}">{{ ucfirst($status) }}</span>
