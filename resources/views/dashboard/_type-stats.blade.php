{{-- Dashboard: per-type stat cards --}}
@if($typeStats->isNotEmpty())
<h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:11px;letter-spacing:1px;">
    By document type
</h6>
<div class="row g-3 mb-4">
    @foreach($typeStats as $stat)
    <div class="col-md-4">
        <a href="{{ route('documents.page', $stat['type']->slug) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-semibold mb-0 text-dark">{{ $stat['type']->name }}</h6>
                        <span class="badge text-bg-primary">{{ $stat['total'] }}</span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($stat['statuses'] as $status => $count)
                            <x-ui.status-badge :status="$status" /> {{ $count }}
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <span class="text-primary small">View {{ $stat['type']->name }} page →</span>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endif
