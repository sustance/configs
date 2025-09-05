<?php
// Get current times in different timezones - using proper timezone identifiers
$hkTime = new DateTime('now', new DateTimeZone('Etc/GMT+8')); // UTC-8 is equivalent to GMT+8
$pacificTime = new DateTime('now', new DateTimeZone('Etc/GMT-7')); // UTC+7 is equivalent to GMT-7
$localTime = new DateTime('now');

// Format the time header
$timeHeader = sprintf(
    "%s H.K. Time -- %s Pacific Time -- %s ENVS Time",
    $hkTime->format('l, M d, h:i:s A'),
    $pacificTime->format('l, M d, h:i:s A'),
    $localTime->format('l, M d, h:i:s A')
);

// Output time header
echo $timeHeader . "<br><br>\n";

// Fetch content from brutalist.report
$url = 'https://brutalist.report/?limit=5';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; PHP script)');
$htmlContent = curl_exec($ch);
curl_close($ch);

if ($htmlContent === false) {
    die('Failed to fetch content from brutalist.report');
}

// Process the HTML content to remove items with hour markers
$filteredContent = '';

// Split by list items and filter
$items = explode('</li>', $htmlContent);
foreach ($items as $item) {
    $item .= '</li>'; // Add the closing tag back
    // Skip items containing hour markers
    if (!preg_match('/\[(0[0-9]|[1-9][0-9]+)h\]/', $item)) {
        $filteredContent .= $item . "\n";
    }
}

// Remove specific sources using the same pattern as the original bash script
$sourcesToRemove = [
    'nytimes',
    'espn', 
    'gamespot',
    'pcgamer',
    'scientificamerican',
    'polygon',
    'lwn'
];

foreach ($sourcesToRemove as $source) {
    $pattern = '/<h3><a href="\/source\/' . preg_quote($source, '/') . '.*?<h3>/s';
    $filteredContent = preg_replace($pattern, '', $filteredContent);
}

// Clean up any leftover empty lines or artifacts
$filteredContent = preg_replace('/<h3><\/h3>/', '', $filteredContent);
$filteredContent = trim($filteredContent);

// Output the filtered content
echo $filteredContent;

// Optional: Add a message at the end
echo "<br><br>Content generated in real-time at: " . date('Y-m-d H:i:s');
?>
