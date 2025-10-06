<?php
// Load .env from home directory
$env_path = '/home/yourusername/.env';

if (!file_exists($env_path)) {
    die('.env file not found!');
}

$env_vars = parse_ini_file($env_path);
$BOT_TOKEN = $env_vars['BOT_TOKEN'];

// Use your token
echo "Token loaded successfully!";
