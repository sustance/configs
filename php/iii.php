<?php 
$head = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/head-land.html');
echo $head;
?>

<div class="bsd">
<H1>TEST DYNO</H1>
</div>

<div class="bsd">
<p>
US.
<a href="https://bsd.tilde.team/"> <b>b</b>sd.tilde.team </a> 
<a href="https://bsd.tilde.team/~identity2">/~i52</a> 
<a href="http://b.identity2.com">b.i.c</a>
<span class="sml">157.90.196.52</span> 
<span style="margin-left:9px;" class="sml"></span> 
</div>




  

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
$tail = file_get_contents('https://raw.githubusercontent.com/sustance/sustance.github.io/refs/heads/main/tail-land.html');
echo $tail;
?>
