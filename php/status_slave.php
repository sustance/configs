<?php
// Determine output format
$format = isset($_GET['format']) ? $_GET['format'] : 'html';

// Security: Optional - Add authentication token check
$allowedToken = 'your-secret-token-here'; // Change this
if (isset($_GET['token']) && $_GET['token'] === $allowedToken) {
    // Token-based access
} else {
    // Optional: Restrict by IP or referrer if needed
}

/**
 * Get version of a command
 */
function getVersion($command, $versionArg = '--version') {
    $output = null;
    $returnCode = null;
    
    // Try to execute the command
    @exec("$command $versionArg 2>&1", $output, $returnCode);
    
    if ($returnCode === 0 && !empty($output)) {
        // Return first line of version output
        return htmlspecialchars($output[0]);
    }
    
    return 'Not found or not accessible';
}

/**
 * Check if a service is running
 */
function isServiceRunning($serviceName) {
    $output = null;
    $returnCode = null;
    
    // Different methods for different systems
    $commands = [
        "systemctl is-active $serviceName 2>/dev/null",
        "service $serviceName status 2>/dev/null",
        "ps aux | grep -v grep | grep $serviceName"
    ];
    
    foreach ($commands as $command) {
        @exec($command, $output, $returnCode);
        if ($returnCode === 0) {
            return 'Running';
        }
    }
    
    return 'Not running';
}

// Collect software information
$software = [];

// Programming Languages
$software['PHP'] = getVersion('php', '--version');
$software['Lua'] = getVersion('lua', '-v');
//$software['Ruby'] = getVersion('ruby', '--version');
//$software['Gem'] = getVersion('gem', '-v');
$software['Shell'] = getVersion('bash', '--version');


// Web Servers
$software['Nginx'] = getVersion('nginx', '-v');
$software['Apache'] = getVersion('apache2', '-v');

// Utilities
$software['Curl'] = getVersion('curl', '--version');
$software['W3m'] = getVersion('w3m', '--version');
$software['Lynx'] = getVersion('lynx', '--version');
$software['Newsboat'] = getVersion('newsboat', '-v');

// Additional system info
$software['Hostname'] = trim(shell_exec('cat /proc/sys/kernel/hostname'));
$software['Username'] = trim(exec('whoami'));
$software['SvrTime'] = date('Y-m-d H:i:s T');
$date = new DateTime('now', new DateTimeZone('HKT'));
$software['HK.Time'] = $date->format('Y-m-d H:i:s T');

// Prepare data for both formats
$hostname = htmlspecialchars(gethostname());
$jsonData = [
    'server' => $hostname,
    'timestamp' => $software['SvrTime'],
    'software' => $software
];

// Write JSON to file
$jsonFilename = 'status_data.json';
file_put_contents($jsonFilename, json_encode($jsonData, JSON_PRETTY_PRINT));

// Output based on format requested
if ($format === 'json') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($jsonData, JSON_PRETTY_PRINT);
} else {
    header('Content-Type: text/html; charset=UTF-8');
    
    // HTML output without embedded styling (CSS classes only)
    echo '<div class="software-status">';
    echo '<table class="software-table">';
    echo '<tr class="table-header">';
    echo '<th class="software-name">Software</th>';
    echo '<th class="software-status">Status for ' . $hostname . '</th>';
    echo '</tr>';
    
    $row = 0;
    foreach ($software as $name => $status) {
        $rowClass = $row % 2 ? 'even-row' : 'odd-row';
        echo "<tr class='$rowClass'>";
        echo "<td class='software-name'><strong>$name</strong></td>";
        echo "<td class='software-value'>$status</td>";
        echo "</tr>";
        $row++;
    }
    
    echo '</table>';
    echo '</div>';
}

// Optional: Add some basic security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
?>
