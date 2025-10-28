#!/usr/bin/env python3
import requests
import json
import time
import os
from datetime import datetime
from pathlib import Path

def get_today_date():
    """Return today's date in YYYYMMDD format"""
    return datetime.now().strftime("%Y%m%d")

def load_server_config():
    """Load server configuration from GitHub - tells us WHERE to fetch data"""
    config_url = "https://raw.githubusercontent.com/sustance/configs/refs/heads/main/status_servers.json"
    response = requests.get(config_url)
    return response.json()['servers']

def fetch_individual_server_data(server_config):
    """Fetch data from individual server status_slave.php?format=json"""
    results = {}
    
    for server in server_config:
        server_name = server['name']
        # CORRECTED: Fetch from status_slave.php with format=json parameter
        status_url = f"https://{server['url']}/~{server['account_name']}/status_data.json?format=json"
        
        print(f"Fetching data from {server_name} ({server['country']})...")
        print(f"  URL: {status_url}")
        
        try:
            start_time = time.time()
            response = requests.get(status_url, timeout=10)
            fetch_time = round(time.time() - start_time, 2)
            
            if response.status_code == 200:
                server_data = response.json()
                results[server_name] = {
                    'country': server['country'],
                    'url': server['url'],
                    'account_name': server['account_name'],
                    'fetch_date': get_today_date(),
                    'fetch_time': fetch_time,
                    'status': 'success',
                    'data': server_data
                }
                print(f"  ✅ Success - fetched {len(server_data.get('software', {}))} software items")
            else:
                results[server_name] = {
                    'country': server['country'],
                    'url': server['url'],
                    'account_name': server['account_name'],
                    'fetch_date': get_today_date(),
                    'fetch_time': fetch_time,
                    'status': f'http_error_{response.status_code}',
                    'data': {}
                }
                print(f"  ❌ HTTP Error {response.status_code}")
                
        except requests.Timeout:
            results[server_name] = {
                'country': server['country'],
                'url': server['url'],
                'account_name': server['account_name'],
                'fetch_date': get_today_date(),
                'fetch_time': None,
                'status': 'timeout',
                'data': {}
            }
            print(f"  ⏰ Timeout")
        except Exception as e:
            results[server_name] = {
                'country': server['country'],
                'url': server['url'],
                'account_name': server['account_name'],
                'fetch_date': get_today_date(),
                'fetch_time': None,
                'status': f'error_{str(e)}',
                'data': {}
            }
            print(f"  💥 Error: {str(e)}")
    
    return results

def create_master_data(server_data):
    """Create hierarchical master data structure"""
    successful_fetches = sum(1 for s in server_data.values() if s['status'] == 'success')
    
    master_data = {
        "metadata": {
            "created_date": get_today_date(),
            "created_timestamp": datetime.now().isoformat(),
            "total_servers": len(server_data),
            "successful_fetches": successful_fetches,
            "failed_fetches": len(server_data) - successful_fetches
        },
        "servers": server_data
    }
    
    return master_data

def save_master_file(master_data, public_html_path="~/public_html"):
    """Save master file with backup rotation"""
    # Expand user path
    public_html_path = os.path.expanduser(public_html_path)
    os.makedirs(public_html_path, exist_ok=True)
    
    today = get_today_date()
    current_file = os.path.join(public_html_path, "status_server_master.json")
    backup_file = os.path.join(public_html_path, f"status_server_master_{today}.json")
    
    # Backup existing file if it exists
    if os.path.exists(current_file):
        os.rename(current_file, backup_file)
        print(f"Backed up previous file to: {backup_file}")
    
    # Save new master file
    with open(current_file, 'w') as f:
        json.dump(master_data, f, indent=2)
    
    print(f"Master file saved to: {current_file}")
    return current_file

def main():
    print("Starting server data collection...")
    print("=" * 60)
    
    # Step 1: Load server configuration (WHERE to fetch from)
    server_config = load_server_config()
    print(f"Loaded configuration for {len(server_config)} servers")
    print("=" * 60)
    
    # Step 2: Fetch data from all servers (THE ACTUAL SOFTWARE DATA)
    server_data = fetch_individual_server_data(server_config)
    
    # Step 3: Create master data structure
    master_data = create_master_data(server_data)
    
    # Step 4: Save master file with backup
    print("=" * 60)
    master_file_path = save_master_file(master_data)
    
    # Summary
    print(f"\nCollection Complete:")
    print(f"Total servers: {master_data['metadata']['total_servers']}")
    print(f"Successful: {master_data['metadata']['successful_fetches']}")
    print(f"Failed: {master_data['metadata']['failed_fetches']}")
    print(f"Master file: {master_file_path}")

if __name__ == "__main__":
    main()
