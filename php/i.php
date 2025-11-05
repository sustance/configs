<?php 
$head = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/head-land.html');
echo $head;
?>



<!--<div class="box">
  <p> Inline in 
<img
  src="https://sustance.github.io/assets/globe.svg"
  alt="Home" height="24" width="24" />
    text</p> 
<img
  src="https://sustance.github.io/assets/globe.svg"
  alt="Home" height="64" width="64" />
</div>
-->


<?php

// Fetch JSON data
$jsonUrl = 'https://raw.githubusercontent.com/sustance/configs/refs/heads/main/status_servers.json';
$jsonData = file_get_contents($jsonUrl);
$data = json_decode($jsonData, true);

// Sort servers by name
usort($data['servers'], function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

?>

<?php

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
    
    echo "</p>\n";
}

echo "</div >";
?>





<?php 
// Fetch and decode the JSON data
$json_url = 'https://thunix.net/~id/status_server_master.json';
$json_data = file_get_contents($json_url);
$data = json_decode($json_data, true);

// Extract unique values for menu
$countries = [];
$software_keys = [];

if (isset($data['servers'])) {
    foreach ($data['servers'] as $server_id => $server_info) {
        // Get country
        if (isset($server_info['country'])) {
            $countries[$server_info['country']] = true;
        }
        
        // Get software keys (from data->software)
        if (isset($server_info['data']['software'])) {
            foreach ($server_info['data']['software'] as $key => $value) {
                $software_keys[$key] = true;
            }
        }
    }
}

// Sort the arrays
ksort($countries);
ksort($software_keys);
?>

<div class="server-filter-container">
    <h4>'f,?,?' by cron only:</h4>
    
    <div class="filter-menu">
        <div class="menu-items">
            <?php foreach (array_keys($countries) as $country): ?>
                <a href="#" class="filter-link" data-type="country" data-value="<?php echo htmlspecialchars($country); ?>">
                    <?php echo htmlspecialchars($country); ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        
        <div class="menu-items">
            <?php foreach (array_keys($software_keys) as $software): ?>
                <a href="#" class="filter-link" data-type="software" data-value="<?php echo htmlspecialchars($software); ?>">
                    <?php echo htmlspecialchars($software); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div id="results" class="results-container" style="display: none;">
        <h3 id="results-title"></h3>
        <table id="results-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Ctry</th>
                    <th></th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody id="results-body">
            </tbody>
        </table>
    </div>
</div>






<pre>
C
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

E
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

F
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

G
NO PHP

H
PHP Fatal error:  Uncaught Error: Call to undefined function curl_exec() in /home/id/eval.php:114
Stack trace:
#0 /home/id/eval.php(42): DynamicExecutionTester->testCurlDirect()
#1 /home/id/eval.php(34): DynamicExecutionTester->testMethod()
#2 /home/id/eval.php(278): DynamicExecutionTester->testAllMethods()
#3 {main}
  thrown in /home/id/eval.php on line 114

 I
allow_url_fopen          : DISABLED
allow_url_include        : DISABLED
curl_extension           : MISSING
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

O J
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback


P
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : MISSING
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

T
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

</pre>




<?php
$tail = file_get_contents('https://raw.githubusercontent.com/sustance/sustance.github.io/refs/heads/main/tail-land.html');
echo $tail;
?>
