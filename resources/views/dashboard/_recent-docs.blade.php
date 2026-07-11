{{-- Dashboard: recent documents table --}}
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
                    <td><span class="badge text-bg-primary">{{ $doc->documentType->name }}</span></td>
                    <td class="text-muted small">{{ $doc->customer?->name ?? '—' }}</td>
                    <td><x-ui.status-badge :status="$doc->status" /></td>
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
