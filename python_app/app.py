# app.py
import os
from flask import Flask, request, Response
from playwright.sync_api import sync_playwright

app = Flask(__name__)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))


def render_pdf(html: str) -> bytes:
    """Render an HTML string to PDF bytes using headless Chromium."""
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page    = browser.new_page()

        # Load HTML directly — base64 images and inline CSS work perfectly
        page.set_content(html, wait_until='networkidle')

        pdf_bytes = page.pdf(
            format            = 'A4',
            print_background  = True,   # renders background colors and images
            margin            = {       # let the .page div control its own padding
                'top':    '0',
                'bottom': '0',
                'left':   '0',
                'right':  '0',
            },
        )

        browser.close()

    return pdf_bytes


@app.route('/health', methods=['GET'])
def health():
    return {'status': 'ok', 'service': 'pdf-generator'}, 200


@app.route('/pdf', methods=['POST'])
def generate_pdf():
    html = request.data.decode('utf-8')

    if not html:
        return {'error': 'No HTML provided'}, 400

    pdf_bytes = render_pdf(html)

    return Response(
        pdf_bytes,
        status=200,
        mimetype='application/pdf',
        headers={'Content-Type': 'application/pdf'}
    )


@app.route('/test', methods=['GET'])
def test_pdf():
    """
    Renders the bundled test.html (with sample CSS: flexbox, CSS variables,
    UTF-8 symbols, table styling) and returns it as a downloadable PDF.
    Visit /test in the browser to sanity-check the whole pipeline.
    """
    test_file = os.path.join(BASE_DIR, 'test.html')

    if not os.path.exists(test_file):
        return {'error': 'test.html not found next to app.py'}, 500

    with open(test_file, 'r', encoding='utf-8') as f:
        html = f.read()

    pdf_bytes = render_pdf(html)

    return Response(
        pdf_bytes,
        status=200,
        mimetype='application/pdf',
        headers={
            'Content-Type': 'application/pdf',
            'Content-Disposition': 'attachment; filename="test.pdf"',
        }
    )
