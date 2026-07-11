{{-- Dashboard: top-level stat cards --}}
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
