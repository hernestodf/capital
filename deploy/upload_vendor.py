#!/usr/bin/env python3
"""Upload vendor.tar.gz + extract script to production"""

import ftputil
import os, sys

def load_env():
    env_file = os.path.join(os.path.dirname(__file__), '..', '.env')
    config = {}
    with open(env_file) as f:
        for line in f:
            line = line.strip()
            if line and '=' in line and not line.startswith('#'):
                k, v = line.split('=', 1)
                config[k.strip()] = v.strip()
    return config

config = load_env()

print(f"Connecting to {config['FTP_HOST']}...")
ftp = ftputil.FTPHost(config['FTP_HOST'], config['FTP_USER'], config['FTP_PASS'])
ftp.chdir(config['FTP_PATH'])

print("Uploading vendor.tar.gz (47MB)...")
ftp.upload('/tmp/vendor.tar.gz', 'vendor.tar.gz')
print("vendor.tar.gz uploaded")

print("Uploading extract_vendor.php...")
ftp.upload('/tmp/extract_vendor.php', 'extract_vendor.php')
print("done")

ftp.close()
print(f"\nAcesse: {config.get('BASE_URL', '').rstrip('/public')}/extract_vendor.php")
