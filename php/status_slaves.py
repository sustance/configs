#!/usr/bin/env python3
"""
Server status monitor - generates status_data.json
Run from cron: */5 * * * * /path/to/status_monitor.py
"""

import subprocess
import socket
import json
from datetime import datetime
from pathlib import Path

def get_version(command, version_arg='--version'):
    """Get version of a command, return '☹' if not found"""
    try:
        result = subprocess.run(
            [command, version_arg],
            capture_output=True,
            text=True,
            timeout=2
        )
        if result.returncode == 0 and result.stdout:
            return result.stdout.strip().split('\n')[0]
        if result.stderr:  # Some commands output to stderr
            return result.stderr.strip().split('\n')[0]
    except (subprocess.TimeoutExpired, FileNotFoundError, Exception):
        pass
    return '☹'

def main():
    # Collect software versions
    software = {
        'PHP': get_version('php'),
        'Lua': get_version('lua', '-v'),
        'Ruby': get_version('ruby'),
        'Gem': get_version('gem', '-v'),
        'Shell': get_version('bash'),
        'Nginx': get_version('nginx', '-v'),
        'Apache': get_version('apache2', '-v'),
        'Curl': get_version('curl')[:50],
        'W3m': get_version('w3m'),
        'Lynx': get_version('lynx'),
        'Newsboat': get_version('newsboat', '-v'),
        'Weechat': get_version('weechat', '-v'),
        'Tldr': get_version('tldr', '-v'),
        'Rtorrent': get_version('rtorrent', '-v'),
        'Nim': get_version('nim', '-v'),
        'Tgpt': get_version('tgpt', '-v'),
        'Mutt': get_version('mutt', '-v'),
    }
    
    # System info
    try:
        with open('/proc/sys/kernel/hostname') as f:
            software['Ⓝ Host'] = f.read().strip()
    except:
        software['Ⓝ Host'] = socket.gethostname()
    
    try:
        software['Ⓝ User'] = subprocess.check_output(['whoami']).decode().strip()
    except:
        software['Ⓝ User'] = '☹'
    
    software['⏱️ Srvr'] = datetime.now().strftime('%Y-%m-%d %H:%M:%S %Z')
    software['⏱️ HKT'] = datetime.now().astimezone().strftime('%Y-%m-%d %H:%M:%S %Z')
    
    try:
        uptime = subprocess.check_output(['uptime', '-p']).decode().strip()
        software['⏱️ Up'] = uptime
    except:
        software['⏱️ Up'] = 'Unknown'
    
    # Build output
    data = {
        'server': socket.gethostname(),
        'timestamp': software['⏱️ Srvr'],
        'software': software
    }
    
    # Write JSON file
    output_file = Path(__file__).parent / 'status_data.json'
    with open(output_file, 'w') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)

if __name__ == '__main__':
    main()
