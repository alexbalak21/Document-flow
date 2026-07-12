{{--
    x-document.table-row
    A single row in the document history table.
    Props: $doc – Document model
--}}
@props(['doc'])

<tr>
    <td class="ps-3 fw-medium">{{ $doc->title }}</td>
    <td><span class="badge text-bg-primary">{{ $doc->documentType->name }}</span></td>
    <td class="text-muted">{{ $doc->reference ?? '—' }}</td>
    <td>
        <span class="badge text-bg-light text-dark border">v{{ $doc->version ?? 1 }}</span>
    </td>
    <td><x-ui.status-badge :status="$doc->status" /></td>
    <td class="text-muted small">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
    <td class="pe-3 text-end">
        <div class="d-flex gap-1 justify-content-end">

            <a href="{{ route('documents.show', $doc) }}" target="_blank"
               class="btn btn-sm btn-outline-primary" title="View">
                <i class="bi bi-eye"></i>
            </a>

            <a href="{{ route('export.document', $doc) }}"
               class="btn btn-sm btn-outline-info" title="Export JSON">
                <i class="bi bi-filetype-json"></i>
            </a>

            <a href="{{ route('pdf.download', $doc) }}"
               class="btn btn-sm btn-outline-danger" title="Download PDF">
                <i class="bi bi-filetype-pdf"></i>
            </a>

            @if($doc->canBeEdited())
            <a href="{{ route('documents.edit', $doc) }}"
               class="btn btn-sm btn-outline-warning" title="Edit draft">
                <i class="bi bi-pencil"></i>
            </a>
            @endif

            @php
                $quoteStatuses   = ['draft','sent','accepted','rejected'];
                $invoiceStatuses = ['draft','sent','paid','cancelled'];
                $statuses = $doc->isQuote() ? $quoteStatuses : $invoiceStatuses;
            @endphp
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                    type="button" data-bs-toggle="dropdown">
                    Status
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($statuses as $s)
                    <li>
                        <form method="POST" action="{{ route('documents.status', $doc) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $s }}">
                            <button type="submit"
                                class="dropdown-item {{ $doc->status === $s ? 'fw-bold' : '' }}">
                                {{ ucfirst($s) }}
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>

            @if($doc->isQuote() && $doc->status === 'accepted' && !$doc->convertedInvoice)
            <form method="POST" action="{{ route('documents.convert', $doc) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" title="Convert to Invoice">
                    <i class="bi bi-arrow-right-circle me-1"></i>Invoice
                </button>
            </form>
            @endif

            @if($doc->isQuote() && $doc->convertedInvoice)
            <a href="{{ route('documents.show', $doc->convertedInvoice) }}"
               class="btn btn-sm btn-outline-info" target="_blank"
               title="View generated invoice">
                <i class="bi bi-receipt"></i>
            </a>
            @endif

        </div>
    </td>
</tr>
