<?php
header('Content-Type: application/json');

// Security: Validate and map server IDs to actual URLs
$allowedServers = [
    0 => 'http://bsd.tilde.team/~identity2/status_slave.php',
    1 => 'http://dimension.sh/~identity2/status_slave.php',
    2 => 'http://ctrl-c.club/~identity2/status_slave.php', 
    3 => 'http://envs.net/~identity2/status_slave.php', 
    4 => 'http://tilde.team/~identity2/status_slave.php'
];

// Get server ID from POST data
$serverId = isset($_POST['server_id']) ? intval($_POST['server_id']) : null;

if ($serverId === null || !isset($allowedServers[$serverId])) {
    echo json_encode(['success' => false, 'error' => 'Invalid server ID']);
    exit;
}

$targetUrl = $allowedServers[$serverId];

// Initialize cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'ServerStatusChecker/1.0',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ]
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(['success' => false, 'error' => 'cURL error: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'HTTP error: ' . $httpCode]);
    exit;
}

// Return the slave's response
echo json_encode([
    'success' => true,
    'html' => $response
]);
?>
