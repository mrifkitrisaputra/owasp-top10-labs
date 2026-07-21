"""
Nac News Portal - Internal API Service
This service is only accessible within the Docker network.
It serves as the target for SSRF exploitation (Flag 5).
"""
from flask import Flask, jsonify, request
import datetime
import os

app = Flask(__name__)

@app.route('/')
def index():
    return jsonify({
        "service": "Nac Internal API",
        "status": "running",
        "message": "This is an internal service. Not accessible from external networks.",
        "endpoints": ["/status", "/health", "/config", "/metrics"]
    })

@app.route('/status')
def status():
    """Flag 5 is here: v3.7.2-ptolemy"""
    return jsonify({
        "service": "nac-internal-api",
        "version": "v3.7.2-ptolemy",
        "uptime": "running",
        "environment": "production",
        "node": "internal-api-01",
        "last_restart": "2024-01-15T03:22:00Z"
    })

@app.route('/health')
def health():
    return jsonify({
        "status": "healthy",
        "database": "connected",
        "cache": "operational",
        "queue": "processing",
        "timestamp": datetime.datetime.utcnow().isoformat()
    })

@app.route('/config')
def config():
    return jsonify({
        "app_name": "Nac News Platform",
        "debug_mode": False,
        "max_connections": 100,
        "cache_ttl": 3600,
        "internal_network": "172.20.0.0/16",
        "services": {
            "web": "web:80",
            "database": "db:3306",
            "api": "internal-api:5000"
        },
        "feature_flags": {
            "new_editor": True,
            "rss_import": True,
            "file_upload_v2": False,
            "advanced_search": True
        }
    })

@app.route('/metrics')
def metrics():
    return jsonify({
        "requests_total": 158420,
        "requests_per_minute": 42,
        "active_sessions": 23,
        "memory_usage_mb": 128,
        "cpu_percent": 12.5,
        "error_rate": 0.02,
        "avg_response_time_ms": 45
    })

@app.route('/admin/debug')
def admin_debug():
    """Hidden endpoint with extra info"""
    return jsonify({
        "debug": True,
        "server_info": {
            "hostname": os.uname().nodename if hasattr(os, 'uname') else "internal-api",
            "pid": os.getpid(),
            "python_version": "3.11",
            "flask_debug": False
        },
        "database_config": {
            "host": "db",
            "port": 3306,
            "database": "nac_news",
            "user": "nac"
        },
        "secrets_note": "API credentials stored in /var/www/secrets/api_credentials.txt on web server"
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
