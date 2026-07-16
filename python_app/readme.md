# PDF Microservice

Tiny Flask service that converts HTML → PDF via WeasyPrint.

## Install

```bash
pip install -r requirements.txt
```

## Run

```bash
# Development
python app.py

# Production (Windows)
waitress-serve --host=127.0.0.1 --port=5001 app:app

# Production (Linux)
gunicorn -w 2 -b 127.0.0.1:5001 app:app
```

## API

### POST /pdf
- Body: raw HTML string (Content-Type: text/html)
- Returns: PDF binary (Content-Type: application/pdf)

### GET /health
- Returns: {"status": "ok"}

## Test
```bash
curl -X POST http://127.0.0.1:5001/pdf \
  -H "Content-Type: text/html" \
  -d "<html><body><h1>Hello</h1></body></html>" \
  --output test.pdf
```