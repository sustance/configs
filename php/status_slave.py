import subprocess
import json
import sys
import os
import socket
from datetime import datetime

def get_version(command, version_arg='--version'):
    try:
        result = subprocess.run([command, version_arg], capture_output=True, text=True, timeout=10)
        if result.returncode == 0 and result.stdout.strip():
            return result.stdout.splitlines()[0].strip()
    except (subprocess.TimeoutExpired, FileNotFoundError, Exception):
        pass
    return 'Not found or not accessible'

def is_service_running(service_name):
    commands = [
        f"systemctl is-active {service_name}",
        f"service {service_name} status",
        f"ps aux | grep -v grep | grep {service_name}"
    ]
    for cmd in commands:
        try:
            result = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=10)
            if result.returncode == 0:
                return 'Running'
        except (subprocess.TimeoutExpired, Exception):
            pass
    return 'Not running'

# Collect software information
software = {}

# Programming Languages
software['PHP'] = get_version('php', '--version')
software['Lua'] = get_version('lua', '-v')
software['Shell'] = get_version('bash', '--version')

# Web Servers
software['Nginx'] = get_version('nginx', '-v')
software['Apache'] = get_version('apache2', '-v')

# Utilities
try:
    curl_result = subprocess.run(['curl', '--version'], capture_output=True, text=True, timeout=10)
    software['Curl'] = curl_result.stdout.splitlines()[0][:50].strip() if curl_result.returncode == 0 else 'Not found or not accessible'
except (subprocess.TimeoutExpired, FileNotFoundError, Exception):
    software['Curl'] = 'Not found or not accessible'

software['W3m'] = get_version('w3m', '--version')
software['Lynx'] = get_version('lynx', '--version')
software['Newsboat'] = get_version('newsboat', '-v')

# Additional system info
software['Ⓝ Host'] = socket.gethostname().strip()
software['Ⓝ User'] = os.getlogin()
server_time = datetime.now()
software['⏱️ Srvr'] = server_time.strftime('%Y-%m-%d %H:%M:%S %Z')

# For HKT (Asia/Hong_Kong) timezone in Python 3.7, use pytz if available, or approximate offset
try:
    import pytz
    hkt_tz = pytz.timezone('Asia/Hong_Kong')
    hkt_time = datetime.now(hkt_tz)
    software['⏱️ HKT'] = hkt_time.strftime('%Y-%m-%d %H:%M:%S %Z')
except ImportError:
    # Fallback: Approximate HKT as UTC+8
    from datetime import timedelta
    hkt_offset = timedelta(hours=8)
    hkt_time = server_time + hkt_offset
    software['⏱️ HKT'] = hkt_time.strftime('%Y-%m-%d %H:%M:%S HKT')

try:
    uptime_result = subprocess.run(['uptime', '-p'], capture_output=True, text=True, timeout=10)
    software['⏱️ Up'] = uptime_result.stdout.strip() if uptime_result.returncode == 0 else 'Unknown'
except (subprocess.TimeoutExpired, FileNotFoundError, Exception):
    software['⏱️ Up'] = 'Unknown'

# Prepare data for JSON
hostname = socket.gethostname()
json_data = {
    'server': hostname,
    'timestamp': software['⏱️ Srvr'],  # Using server time
    'software': software
}

# Write JSON to file
with open('status_data.json', 'w', encoding='utf-8') as f:
    json.dump(json_data, f, indent=2, ensure_ascii=False)

# Generate HTML content
html_content = f'''<div class="software-status">
<table class="software-table">
<tr class="table-header">
<th class="software-name">Software</th>
<th class="software-status">Status for {hostname}</th>
</tr>'''

row = 0
for name, status in software.items():
    row_class = 'even-row' if row % 2 else 'odd-row'
    html_content += f'<tr class="{row_class}">'
    html_content += f'<td class="software-name"><strong>{name}</strong></td>'
    html_content += f'<td class="software-value">{status}</td>'
    html_content += '</tr>'
    row += 1

html_content += '</table></div>'

# Write HTML to file
with open('status_data.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

# Determine output format
if len(sys.argv) > 1 and sys.argv[1] == 'json':
    print(json.dumps(json_data, indent=2, ensure_ascii=False))
else:
    print(html_content)
