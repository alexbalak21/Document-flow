<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->title }}</title>
    <style>
        /* ── SCREEN ─────────────────────────────────────────────────────── */
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

            .df-btn:hover {
                background: rgba(255,255,255,.2);
                color: #fff;
            }

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
                box-shadow:
                    0 0 0 1px rgba(0,0,0,.08),
                    0 8px 40px rgba(0,0,0,.4);
                border-radius: 1px;
                overflow: hidden;
            }

            /* iframe fills the A4 box */
            .df-a4 iframe {
                width: 100%;
                height: 297mm;
                border: none;
                display: block;
            }
        }

        /* ── PRINT ──────────────────────────────────────────────────────── */
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

    {{-- Toolbar --}}
    <div class="df-toolbar">
        <span class="doc-title">{{ $document->title }}</span>
        <a href="javascript:history.back()" class="df-btn">← Back</a>
        <a href="{{ route('documents.history') }}" class="df-btn">History</a>
        <button onclick="printDoc()" class="df-btn df-btn-print">⎙ Print / Save PDF</button>
    </div>

    {{-- A4 page rendered inside an iframe so its own CSS is fully isolated --}}
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
                frame.style.height = Math.max(h, 1122) + 'px'; // 1122px ≈ 297mm at 96dpi
                document.querySelector('.df-a4').style.minHeight = frame.style.height;
            } catch(e) {}
        }

        function printDoc() {
            document.getElementById('doc-frame').contentWindow.print();
        }
    </script>

</body>
</html>
