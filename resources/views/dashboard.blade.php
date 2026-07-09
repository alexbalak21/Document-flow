@extends('layouts.auth')

@section('title', 'Dashboard')

@section('content')

<h4 class="fw-semibold mb-4">Dashboard</h4>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle p-3">
                    <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold">{{ $templateCount }}</div>
                    <div class="text-muted small">Templates</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success-subtle p-3">
                    <i class="bi bi-people text-success fs-4"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold">{{ $customerCount }}</div>
                    <div class="text-muted small">Customers</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning-subtle p-3">
                    <i class="bi bi-box-seam text-warning fs-4"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold">{{ $productCount }}</div>
                    <div class="text-muted small">Products</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('documents.history') }}" class="text-decoration-none text-dark">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info-subtle p-3">
                        <i class="bi bi-archive text-info fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $documentCount }}</div>
                        <div class="text-muted small">Documents</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Per-type stats --}}
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
                            @php $color = \App\Models\Document::$statusColors[$status] ?? 'secondary'; @endphp
                            <span class="badge text-bg-{{ $color }}">
                                {{ ucfirst($status) }}: {{ $count }}
                            </span>
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

{{-- Recent documents --}}
@if($recentDocs->isNotEmpty())
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-semibold text-muted text-uppercase mb-0" style="font-size:11px;letter-spacing:1px;">
        Recent documents
    </h6>
    <a href="{{ route('documents.history') }}" class="btn btn-sm btn-outline-secondary">View all</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Title</th>
                    <th>Type</th>
                    <th>Customer</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="pe-3 text-end"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentDocs as $doc)
                <tr>
                    <td class="ps-3 fw-medium">{{ $doc->reference ?? $doc->title }}</td>
                    <td>
                        <span class="badge text-bg-primary">{{ $doc->documentType->name }}</span>
                    </td>
                    <td class="text-muted small">{{ $doc->customer?->name ?? '—' }}</td>
                    <td>
                        @php $color = \App\Models\Document::$statusColors[$doc->status] ?? 'secondary'; @endphp
                        <span class="badge text-bg-{{ $color }}">{{ ucfirst($doc->status) }}</span>
                    </td>
                    <td class="text-muted small">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                    <td class="pe-3 text-end">
                        <a href="{{ route('documents.show', $doc) }}" target="_blank"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
