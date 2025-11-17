<!DOCTYPE html>
<html lang="en">
<head>
<title>IDNIX</title>
<link rel="stylesheet" href="https://sustance.github.io/head.css" />
<style>
/* Basic styling to ensure the results are visible */
.results-container {
    margin-top: 20px;
    padding: 10px;
}

#results-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

#results-table th,
#results-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

#results-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.filter-link {
    display: inline-block;
    margin: 5px;
    padding: 5px 10px;
    text-decoration: none;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.filter-link:hover {
    background-color: #f0f0f0;
}

.menu-items {
    margin-bottom: 15px;
}
</style>
</head>
<body>

<svg width="900" height="75.390625" version="1.1" id="svg1">
  <text x="-2.5" y="73.109375" font-family="Verdana" font-size="96px" fill="rgba(255, 99, 132, 0.1)" stroke="#000000" stroke-width="1.5" id="text1" style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:96px;font-family:Georgia;">
    <tspan> 🖧Servers</tspan>
  </text>
</svg>

<p>Works on H but not on E</p>

<!-- Fetch and process data -->
<script>
let serverData = null;
let sortedCountries = [];
let sortedSoftware = [];

// Fetch the JSON data
fetch('https://thunix.net/~id/status_server_master.json')
    .then(response => response.json())
    .then(data => {
        serverData = data;
        
        // Extract unique values for menu
        const countries = {};
        const software_keys = {};

        if (serverData.servers) {
            for (const [serverId, serverInfo] of Object.entries(serverData.servers)) {
                if (serverInfo.country) {
                    countries[serverInfo.country] = true;
                }
                
                if (serverInfo.data && serverInfo.data.software) {
                    for (const key of Object.keys(serverInfo.data.software)) {
                        software_keys[key] = true;
                    }
                }
            }
        }

        // Sort and store
        sortedCountries = Object.keys(countries).sort();
        sortedSoftware = Object.keys(software_keys).sort();
        
        // Build the menus
        buildMenus();
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        document.body.innerHTML += '<p style="color: red;">Error loading server data. Please check the console.</p>';
    });

function buildMenus() {
    const countryMenu = document.getElementById('country-menu');
    const softwareMenu = document.getElementById('software-menu');
    
    // Clear existing content
    countryMenu.innerHTML = '';
    softwareMenu.innerHTML = '';
    
    // Build country menu
    sortedCountries.forEach(country => {
        const link = document.createElement('a');
        link.href = '#';
        link.className = 'filter-link';
        link.dataset.type = 'country';
        link.dataset.value = country;
        link.textContent = country;
        countryMenu.appendChild(link);
    });

    // Build software menu
    sortedSoftware.forEach(software => {
        const link = document.createElement('a');
        link.href = '#';
        link.className = 'filter-link';
        link.dataset.type = 'software';
        link.dataset.value = software;
        link.textContent = software;
        softwareMenu.appendChild(link);
    });
    
    // Add click handlers to all filter links
    document.querySelectorAll('.filter-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const filterType = this.dataset.type;
            const filterValue = this.dataset.value;
            
            filterServers(filterType, filterValue);
        });
    });
}
</script>

<div class="server-filter-container">
    <div class="filter-menu">
        <h3>Filter by Country:</h3>
        <div class="menu-items" id="country-menu"></div>
        
        <h3>Filter by Software:</h3>
        <div class="menu-items" id="software-menu"></div>
    </div>
    
    <div id="results" class="results-container" style="display: none;">
        <h3 id="results-title"></h3>

        <table id="results-table">
            <thead>
                <tr>
                    <th>Server</th>
                    <th>Ctry</th>
                    <th>Software</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody id="results-body"></tbody>
        </table>
    </div>
</div>

<script>
function filterServers(filterType, filterValue) {
    const resultsDiv = document.getElementById('results');
    const resultsTitle = document.getElementById('results-title');
    const resultsBody = document.getElementById('results-body');
    
    // Clear previous results
    resultsBody.innerHTML = '';
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

</body>
</html>
