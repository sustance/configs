<?php
// Fetch JSON data
$jsonUrl = 'https://raw.githubusercontent.com/sustance/configs/refs/heads/main/status_servers.json';
$jsonData = file_get_contents($jsonUrl);
$data = json_decode($jsonData, true);

// Sort servers by name
usort($data['servers'], function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
//////////////////////
echo "<div class='box idx'>";
// Output each server
foreach ($data['servers'] as $server) {
    $osClass = $server['os'] ?? 'Linux';
    echo "<p>";    
    // Basic server info
    echo "<b class=\"$osClass\">{$server['name']}</b>  ";    
    echo "<span>{$server['country']}</span> ";    
    echo "<a href=\"http://{$server['host_url']}\">{$server['host_url']}</a> ";    
    echo "<a href=\"http://{$server['url_own']}\">i.c.{$server['name']}</a> ";    
    echo "<a href=\"http://{$server['url']}\">{$server['acc_name_s']}</a> ";    
    echo "{$server['ip_address']} ";
    
    // Links from links_http array
    if (isset($server['links']) && is_array($server['links'])) {
        foreach ($server['links'] as $link) {
            $linkUrl = $server['url'] . '/' . $link . '.php';
            echo "<a href=\"http://$linkUrl\">$link</a> ";
        }
    }

    echo "<span>{$server['apps_running']}</span> ";
    
    echo "</p>\n</div>";
}
?>
