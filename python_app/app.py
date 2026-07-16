from flask import Flask, request, Response
from playwright.sync_api import sync_playwright

app = Flask(__name__)

@app.route('/health', methods=['GET'])
def health():
    return {'status': 'ok', 'service': 'pdf-generator'}, 200

@app.route('/pdf', methods=['POST'])
def generate_pdf():
    html = request.data.decode('utf-8')

    if not html:
        return {'error': 'No HTML provided'}, 400

    with sync_playwright() as p:
        browser = p.chromium.launch()
        page    = browser.new_page()

        # Load HTML directly — base64 images and inline CSS work perfectly
        page.set_content(html, wait_until='networkidle')

        pdf_bytes = page.pdf(
            format          = 'A4',
            print_background = True,   # renders background colors and images
            margin          = {        # let the .page div control its own padding
                'top':    '0',
                'bottom': '0',
                'left':   '0',
                'right':  '0',
            },
        )

        browser.close()

    return Response(
        pdf_bytes,
        status=200,
        mimetype='application/pdf',
        headers={'Content-Type': 'application/pdf'}
    )

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5001, debug=False)