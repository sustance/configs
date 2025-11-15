<?php

// Fetch head section data
$head = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/head-land.html');
echo $head;

// Fetch ServerList section data
$slist = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/head-land.html');
echo $slist;

// Fetch JSON data
//$jsonUrl ';

$jsonData = file_get_contents($jsonUrl);
$data = json_decode($jsonData, true);

// Sort servers by name

usort($data['servers'], function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

// Output server list

echo "<div class='box idx'>";
// Output each server
foreach ($data['servers'] as $server) {
    $osClass = $server['os'] ?? 'Linux';
    echo "<p>";    
    // Basic server info
    echo "<span class='$osClass'><tt>{$server['name']}</tt></span> ";    
    echo "<span class='sml'>{$server['country']}</span>";
    echo "<span>{$server['Vim']}</span> ";
    echo "<span>{$server['PHP']}</span> ";
  //popup start
    echo "<span class='tooltip' data-tooltip=\"{$server['ip_address']} os:{$server['os']}\">❔ </span>";
  //popup end
  echo "<a href=\"http://{$server['host_url']}\">{$server['host_url']}</a> ";    
    echo "<a href=\"http://{$server['url_own']}\">{$server['name']}.𝕚.𝕔</a> ";    
    echo "<a href=\"http://{$server['url']}\">{$server['acc_name_s']}</a> ";    
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

<pre>                   C E F O J T    I P
allow_url_fopen    : ENABLED      : DISABLED
allow_url_include  : DISABLED     : DISABLED
curl_extension     : AVAILABLE    : MISSING
write_permissions  : WRITABLE     : WRITABLE
ALL_DYNAMIC_METHODS_FAIL cron fallback 
    G    NO PHP
    H    PHP Fatal error: </pre>

<?php
$tail = file_get_contents('https://raw.githubusercontent.com/sustance/sustance.github.io/refs/heads/main/tail-land.html');
echo $tail;
?>
