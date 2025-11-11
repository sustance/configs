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
<script>
// Store the JSON data in JavaScript
const serverData = <?php echo json_encode($data); ?>;

// Add click handlers to all filter links
document.querySelectorAll('.filter-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        
        const filterType = this.dataset.type;
        const filterValue = this.dataset.value;
        
        filterServers(filterType, filterValue);
    });
});

function filterServers(filterType, filterValue) {
    const resultsDiv = document.getElementById('results');
    const resultsTitle = document.getElementById('results-title');
    const resultsBody = document.getElementById('results-body');
    
    // Clear previous results
    resultsBody.innerHTML = '';
    
    // Filter and display results
    let rowCount = 0;
    
    if (serverData.servers) {
        for (const [serverId, serverInfo] of Object.entries(serverData.servers)) {
            // Skip if no valid data
            if (serverInfo.status !== 'success' || !serverInfo.data || !serverInfo.data.software) {
                continue;
            }
            
            if (filterType === 'country') {
                // Filter by country
                if (serverInfo.country === filterValue) {
                    // Add all software entries for this server
                    for (const [softKey, softValue] of Object.entries(serverInfo.data.software)) {
                        addResultRow(resultsBody, serverId, serverInfo.country, softKey, softValue);
                        rowCount++;
                    }
                }
            } else if (filterType === 'software') {
                // Filter by software key
                if (serverInfo.data.software[filterValue]) {
                    addResultRow(resultsBody, serverId, serverInfo.country, filterValue, serverInfo.data.software[filterValue]);
                    rowCount++;
                }
            }
        }
    }
    
    // Update title and show results
    resultsTitle.textContent = `Result: ${filterType}: ${filterValue} (${rowCount})`;
    resultsDiv.style.display = 'block';
    
    // Scroll to results
    resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function addResultRow(tbody, serverId, country, softKey, softValue) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${escapeHtml(serverId)}</td>
        <td>${escapeHtml(country)}</td>
        <td>${escapeHtml(softKey)}</td>
        <td>${escapeHtml(softValue)}</td>
    `;
    tbody.appendChild(row);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

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
$img = file_get_contents('https://sustance.github.io/svg/Linux.svg');
echo $img;
?>

<!--
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
-->


<?php
$tail = file_get_contents('https://raw.githubusercontent.com/sustance/sustance.github.io/refs/heads/main/tail-land.html');
echo $tail;
?>
