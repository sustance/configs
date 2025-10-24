<?php
header('Content-Type: text/html; charset=UTF-8');

// Security: Optional - Add authentication token check
$allowedToken = 'your-secret-token-here'; // Change this
if (isset($_GET['token']) && $_GET['token'] === $allowedToken) {
    // Token-based access
} else {
    // Optional: Restrict by IP or referrer if needed
    // For simplicity, we'll make it publicly accessible but you should secure this
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
$software['Shell'] = getVersion('bash', '--version');
//$software['Gem'] = getVersion('gem', '-v');

// Web Servers
$software['Nginx'] = getVersion('nginx', '-v');
$software['Apache'] = getVersion('apache2', '-v');

// Utilities
$fullVersion = getVersion('curl', '--version');
$software['Curl'] = substr($fullVersion, 0, 100);
$software['Curl'] = getVersion('curl', '--version');

$software['W3m'] = getVersion('w3m', '--version');
$software['Lynx'] = getVersion('lynx', '--version');
$software['Newsboat'] = getVersion('newsboat', '-v');
//$software['Weechat'] = getVersion('weechat', '-v');
//$software['Tldr'] = getVersion('tldr', '-v');
//$software['Rtorrent'] = getVersion('rtorrent', '-v');
//$software['Nim'] = getVersion('nim', '-v');
//$software['Tgpt'] = getVersion('tgpt', '-v');
//$software['Mutt'] = getVersion('mutt', '-v');

// Additional system info
$software['Hostname'] = trim(shell_exec('cat /proc/sys/kernel/hostname'));
$software['Username'] = trim(exec('whoami'));
$software['SvrTime'] = date('Y-m-d H:i:s T');
//$software['HK.Time'] = TZ=UTC-8 date('Y-m-d H:i:s T');
$date = new DateTime('now', new DateTimeZone('HKT'));
$software['HK.Time'] = $date->format('Y-m-d H:i:s T');
//$software['Uptime'] = @trim(@shell_exec('uptime -p 2>/dev/null') ?: 'Unknown');

// Generate HTML output
echo '<div class="software-status">';
//echo '<h4>Software Status for ' . htmlspecialchars(gethostname()) . '</h4>';
echo '<table style="width: 100%; border-collapse: collapse;">';
echo '<tr style="background: #e9e9e9;">
<th style="text-align: left; padding: 8px;">Software ' . htmlspecialchars(gethostname()) . '</th>
<th style="text-align: left; padding: 8px;">Status</th>
</tr>';

$row = 0;
foreach ($software as $name => $status) {
    $bgColor = $row % 2 ? '#f9f9f9' : '#ffffff';
    echo "<tr style='background: $bgColor;'>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #eee;'><strong>$name</strong></td>";
    echo "<td style='padding: 8px; border-bottom: 1px solid #eee;'>$status</td>";
    echo "</tr>";
    $row++;
}

echo '</table>';
echo '</div>';

// Optional: Add some basic security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
?>
