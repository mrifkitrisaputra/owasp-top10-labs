"""
NAC Cafe Internal IoT Monitoring Service
This service runs internally and should NOT be accessible from public internet.
Used by admin panel for equipment monitoring.
"""

from flask import Flask, jsonify
import datetime

app = Flask(__name__)

@app.route('/')
def index():
    return jsonify({
        "service": "NAC Cafe Internal Monitoring",
        "version": "1.0.3",
        "status": "running",
        "warning": "This is an internal service. Not for public access."
    })

@app.route('/status')
def status():
    return jsonify({
        "timestamp": datetime.datetime.now().isoformat(),
        "devices": [
            {
                "id": "DEV-001",
                "name": "espresso_machine_model_x9000",
                "type": "Espresso Machine",
                "brand": "La Marzocco",
                "status": "online",
                "temperature": "93.5°C",
                "pressure": "9.2 bar",
                "last_maintenance": "2024-01-15",
                "next_maintenance": "2024-07-15",
                "location": "Main Bar"
            },
            {
                "id": "DEV-002",
                "name": "grinder_pro_v3",
                "type": "Coffee Grinder",
                "brand": "Mahlkoenig",
                "status": "online",
                "rpm": 1400,
                "last_calibration": "2024-03-01",
                "location": "Main Bar"
            },
            {
                "id": "DEV-003",
                "name": "cold_brew_tower_alpha",
                "type": "Cold Brew System",
                "brand": "Toddy",
                "status": "brewing",
                "brew_time_remaining": "6h 30m",
                "batch_id": "CB-2024-0892",
                "location": "Prep Area"
            }
        ],
        "environment": {
            "kitchen_temp": "24.5°C",
            "humidity": "65%",
            "storage_temp": "18.2°C"
        },
        "network": {
            "internal_ip": "172.20.0.3",
            "gateway": "172.20.0.1",
            "dns": "172.20.0.1"
        }
    })

@app.route('/health')
def health():
    return jsonify({"status": "healthy", "uptime": "47d 12h 33m"})

@app.route('/logs')
def logs():
    return jsonify({
        "recent_logs": [
            {"time": "2024-08-25 14:30:00", "level": "INFO", "message": "Espresso machine temperature stable"},
            {"time": "2024-08-25 14:00:00", "level": "INFO", "message": "Cold brew batch CB-2024-0892 started"},
            {"time": "2024-08-25 13:45:00", "level": "WARNING", "message": "Grinder hopper level low - refill needed"},
            {"time": "2024-08-25 12:00:00", "level": "INFO", "message": "Daily calibration completed"},
            {"time": "2024-08-25 08:00:00", "level": "INFO", "message": "System startup - all devices initialized"}
        ]
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
