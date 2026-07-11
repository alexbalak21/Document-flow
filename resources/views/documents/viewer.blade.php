<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->title }}</title>
    <style>
        @media screen {
            *, *::before, *::after { box-sizing: border-box; }

            html, body {
                margin: 0;
                padding: 0;
                background: #4b5563;
                min-height: 100vh;
                font-family: 'Helvetica Neue', Arial, sans-serif;
            }

            .df-toolbar {
                position: fixed;
                top: 0; left: 0; right: 0;
                height: 50px;
                background: #1e2533;
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 0 20px;
                z-index: 1000;
                box-shadow: 0 2px 10px rgba(0,0,0,.4);
            }

            .df-toolbar .doc-title {
                font-size: 13px;
                font-weight: 600;
                color: #fff;
                flex: 1;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .df-toolbar .doc-version {
                font-size: 11px;
                color: rgba(255,255,255,.5);
                background: rgba(255,255,255,.08);
                border: 1px solid rgba(255,255,255,.12);
                border-radius: 4px;
                padding: 2px 8px;
                white-space: nowrap;
            }

            .df-toolbar .doc-status {
                font-size: 11px;
                border-radius: 4px;
                padding: 2px 8px;
                white-space: nowrap;
                font-weight: 600;
            }

            .df-btn {
                font-size: 12.5px;
                color: rgba(255,255,255,.85);
                background: rgba(255,255,255,.1);
                border: 1px solid rgba(255,255,255,.18);
                border-radius: 5px;
                padding: 5px 14px;
                cursor: pointer;
                text-decoration: none;
                white-space: nowrap;
                transition: background .15s, color .15s;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .df-btn:hover { background: rgba(255,255,255,.2); color: #fff; }

            .df-btn-edit {
                background: rgba(234,179,8,.2);
                border-color: rgba(234,179,8,.4);
                color: #fde047;
            }

            .df-btn-edit:hover { background: rgba(234,179,8,.35); color: #fef08a; }

            .df-btn-print {
                background: #1a56db;
                border-color: #1a56db;
                color: #fff;
            }

            .df-btn-print:hover { background: #1648c0; }

            .df-page-shell {
                padding-top: 70px;
                padding-bottom: 48px;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                min-height: 100vh;
            }

            .df-a4 {
                width: 210mm;
                min-height: 297mm;
                background: #fff;
                box-shadow: 0 0 0 1px rgba(0,0,0,.08), 0 8px 40px rgba(0,0,0,.4);
                border-radius: 1px;
                overflow: hidden;
            }

            .df-a4 iframe {
                width: 100%;
                height: 297mm;
                border: none;
                display: block;
            }
        }

        @media print {
            .df-toolbar    { display: none !important; }
            .df-page-shell { padding: 0 !important; }
            .df-a4         { box-shadow: none !important; width: 100% !important; }
            .df-a4 iframe  { height: 100vh; }
            html, body     { background: #fff !important; }
        }
    </style>
</head>
<body>

    <div class="df-toolbar">
        <span class="doc-title">{{ $document->title }}</span>

        {{-- Version badge --}}
        <span class="doc-version">v{{ $document->version ?? 1 }}</span>

        {{-- Status badge --}}
        @php
            $statusColors = [
                'draft'     => 'background:rgba(107,114,128,.3); color:#e5e7eb;',
                'sent'      => 'background:rgba(59,130,246,.3); color:#93c5fd;',
                'accepted'  => 'background:rgba(34,197,94,.3);  color:#86efac;',
                'rejected'  => 'background:rgba(239,68,68,.3);  color:#fca5a5;',
                'invoiced'  => 'background:rgba(6,182,212,.3);  color:#67e8f9;',
                'paid'      => 'background:rgba(255,255,255,.15); color:#fff;',
                'cancelled' => 'background:rgba(234,179,8,.3);  color:#fde047;',
            ];
            $statusStyle = $statusColors[$document->status] ?? '';
        @endphp
        <span class="doc-status" style="{{ $statusStyle }}">{{ ucfirst($document->status) }}</span>

        <a href="javascript:history.back()" class="df-btn">← Back</a>
        <a href="{{ route('documents.history') }}" class="df-btn">History</a>

        {{-- Edit button — only for drafts --}}
        @if($document->canBeEdited())
        <a href="{{ route('documents.edit', $document) }}" class="df-btn df-btn-edit">
            ✎ Edit Draft
        </a>
        @endif

        <a href="{{ route('export.document', $document) }}" class="df-btn">
            ↓ Export JSON
        </a>
        <button onclick="printDoc()" class="df-btn df-btn-print">⎙ Print / Save PDF</button>
    </div>

    <div class="df-page-shell">
        <div class="df-a4">
            <iframe id="doc-frame"
                src="{{ route('documents.raw', $document) }}"
                onload="autoHeight(this)">
            </iframe>
        </div>
    </div>

    <script>
        function autoHeight(frame) {
            try {
                const h = frame.contentDocument.documentElement.scrollHeight;
                frame.style.height = Math.max(h, 1122) + 'px';
                document.querySelector('.df-a4').style.minHeight = frame.style.height;
            } catch(e) {}
        }

        function printDoc() {
            document.getElementById('doc-frame').contentWindow.print();
        }
    </script>

</body>
</html>
