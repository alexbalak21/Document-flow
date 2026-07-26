from flask import Flask

app = Flask(__name__)

@app.route("/")
def hello_flask():
    return "<h1>Hello from Flask!</h1>"

@app.route("/api")
def api():
    return "<h1>Hello from Flask API!</h1>"

@app.route("/health")
def health():
    return "<h1>Flask API is healthy!</h1>"