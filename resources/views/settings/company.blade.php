@extends('layouts.auth')

@section('title', 'Company Settings')

@section('content')
<div class="container py-4" style="max-width:800px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="fw-semibold mb-0">Company Settings</h4>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Identity --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-building me-2 text-primary"></i>Identity
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Company Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $company['name'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Legal Form</label>
                        <input type="text" name="legal_form" class="form-control" value="{{ $company['legal_form'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Share Capital</label>
                        <input type="text" name="share_capital" class="form-control" value="{{ $company['share_capital'] }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Company Logo</label>
                        @if($logo)
                        <div class="mb-2 p-3 border rounded d-flex align-items-center gap-3">
                            <img src="{{ $logo->data_uri }}" alt="Current logo"
                                 style="max-height:60px; max-width:200px; object-fit:contain;">
                            <div>
                                <div class="small fw-medium">{{ $logo->filename }}</div>
                                <div class="small text-muted">{{ $logo->mime_type }}</div>
                                <div class="form-check mt-1">
                                    <input type="checkbox" class="form-check-input" name="delete_logo" id="delete_logo" value="1">
                                    <label class="form-check-label small text-danger" for="delete_logo">
                                        Remove logo
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <div class="form-text">PNG, JPG or SVG. Max 2 MB. Will be embedded as base64 in documents.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-geo-alt me-2 text-primary"></i>Address
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium">Street</label>
                        <input type="text" name="street" class="form-control" value="{{ $company['street'] }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">City</label>
                        <input type="text" name="city" class="form-control" value="{{ $company['city'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">ZIP</label>
                        <input type="text" name="zip" class="form-control" value="{{ $company['zip'] }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-medium">Country</label>
                        <input type="text" name="country" class="form-control" value="{{ $company['country'] }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Official identifiers --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-fingerprint me-2 text-primary"></i>Official Identifiers
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">SIREN</label>
                        <input type="text" name="siren" class="form-control" value="{{ $company['siren'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">SIRET</label>
                        <input type="text" name="siret" class="form-control" value="{{ $company['siret'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">VAT Number</label>
                        <input type="text" name="vat_number" class="form-control" value="{{ $company['vat_number'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">EORI</label>
                        <input type="text" name="eori" class="form-control" value="{{ $company['eori'] }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-envelope me-2 text-primary"></i>Contact
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $company['email'] }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Website</label>
                        <input type="url" name="website" class="form-control" value="{{ $company['website'] }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Accounting defaults --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-calculator me-2 text-primary"></i>Accounting Defaults
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Currency</label>
                        <input type="text" name="default_currency" class="form-control" value="{{ $company['default_currency'] }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Currency Symbol</label>
                        <input type="text" name="default_currency_symbol" class="form-control" value="{{ $company['default_currency_symbol'] }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Default VAT Rate (%)</label>
                        <input type="number" name="default_vat_rate" class="form-control" value="{{ $company['default_vat_rate'] }}" step="0.1" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Invoice Due Days</label>
                        <input type="number" name="default_invoice_due_days" class="form-control" value="{{ $company['default_invoice_due_days'] }}" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Quote Valid Days</label>
                        <input type="number" name="default_quote_valid_days" class="form-control" value="{{ $company['default_quote_valid_days'] }}" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Default Payment Method</label>
                        <input type="text" name="default_payment_method" class="form-control" value="{{ $company['default_payment_method'] }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Legal mentions --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-file-text me-2 text-primary"></i>Legal Mentions
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium">VAT Mention</label>
                        <textarea name="vat_mention" class="form-control" rows="2">{{ $company['vat_mention'] }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Late Payment Rate (%)</label>
                        <input type="number" name="late_payment_rate" class="form-control" value="{{ $company['late_payment_rate'] }}" step="0.1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Late Payment Flat Fee (€)</label>
                        <input type="number" name="late_payment_flat_fee" class="form-control" value="{{ $company['late_payment_flat_fee'] }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Late Payment Text</label>
                        <input type="text" name="late_payment_text" class="form-control" value="{{ $company['late_payment_text'] }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Late Payment Fee Text</label>
                        <input type="text" name="late_payment_fee_text" class="form-control" value="{{ $company['late_payment_fee_text'] }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Terms Text</label>
                        <input type="text" name="terms_text" class="form-control" value="{{ $company['terms_text'] }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-floppy me-1"></i>Save Settings
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </form>
</div>
@endsection
