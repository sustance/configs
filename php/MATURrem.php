<?php
// Simple PHP script to generate maturity reminders with 3-business-day alerts
// Call via web: http://yourserver.com/thisfile.php
// Outputs in <pre> for monospace readability on mobile (narrow columns, no wrap)
// No data validation; assumes well-formed input
// Debug: Uncomment echo statements for troubleshooting
// Date format: CSV uses YMMDD (5 digits: units digit of year (2020+units), MM (2 w/ leading 0), DD (2 w/ leading 0))
// Alerts: 3 business days prior, formatted same YMMDD
// Only future maturities (> 2025-10-05) and their future alerts included
// Fixed: Country from $parts[4] (C field); HK 2025 holiday corrected to Oct 29

// Embedded holiday data (YYYY-MM-DD format)
$HOLIDAYS = [
    'HK' => [  // Hong Kong
        2025 => ['2025-01-01', '2025-01-29', '2025-01-30', '2025-01-31', '2025-04-04', '2025-04-18', '2025-04-19', '2025-04-21', '2025-05-01', '2025-05-05', '2025-05-31', '2025-07-01', '2025-10-01', '2025-10-02', '2025-10-03', '2025-10-29', '2025-12-25', '2025-12-26'],  // Fixed: Oct 29 for Chung Yeung
        2026 => ['2026-01-01', '2026-02-17', '2026-02-18', '2026-02-19', '2026-04-05', '2026-04-03', '2026-04-04', '2026-04-06', '2026-05-01', '2026-05-15', '2026-06-26', '2026-07-01', '2026-10-01', '2026-10-02', '2026-10-05', '2026-10-08', '2026-12-25', '2026-12-26'],
        2027 => ['2027-01-01', '2027-02-06', '2027-02-07', '2027-02-08', '2027-04-05', '2027-03-26', '2027-03-27', '2027-03-29', '2027-05-01', '2027-05-06', '2027-06-15', '2027-07-01', '2027-10-01', '2027-10-04', '2027-10-05', '2027-10-27', '2027-12-25', '2027-12-26'],
        2028 => ['2028-01-01', '2028-01-26', '2028-01-27', '2028-01-28', '2028-04-04', '2028-04-14', '2028-04-15', '2028-04-17', '2028-05-01', '2028-05-02', '2028-05-29', '2028-07-01', '2028-10-01', '2028-10-02', '2028-10-04', '2028-10-26', '2028-12-25', '2028-12-26'],
        2029 => ['2029-01-01', '2029-02-13', '2029-02-14', '2029-02-15', '2029-04-05', '2029-03-30', '2029-03-31', '2029-04-02', '2029-05-01', '2029-05-20', '2029-06-16', '2029-07-01', '2029-07-02', '2029-09-22', '2029-10-01', '2029-10-16', '2029-12-25', '2029-12-26'],
        2030 => ['2030-01-01', '2030-02-02', '2030-02-04', '2030-02-05', '2030-04-05', '2030-04-19', '2030-04-22', '2030-05-01', '2030-05-09', '2030-06-05', '2030-07-01', '2030-09-13', '2030-10-01', '2030-10-05', '2030-12-25', '2030-12-26']
    ],
    'CN' => [  // China
        2025 => ['2025-01-01', '2025-01-28', '2025-01-29', '2025-01-30', '2025-01-31', '2025-04-04', '2025-04-05', '2025-05-01', '2025-05-02', '2025-05-03', '2025-05-04', '2025-05-31', '2025-09-15', '2025-10-01', '2025-10-02', '2025-10-03', '2025-10-04', '2025-10-05', '2025-10-06', '2025-10-07'],
        2026 => ['2026-01-01', '2026-02-16', '2026-02-17', '2026-02-18', '2026-02-19', '2026-02-20', '2026-04-04', '2026-04-05', '2026-05-01', '2026-06-26', '2026-09-14', '2026-10-01', '2026-10-02', '2026-10-03', '2026-10-04', '2026-10-05', '2026-10-06', '2026-10-07'],
        2027 => ['2027-01-01', '2027-02-05', '2027-02-06', '2027-02-07', '2027-02-08', '2027-02-09', '2027-04-05', '2027-04-06', '2027-05-01', '2027-06-15', '2027-09-13', '2027-10-01', '2027-10-02', '2027-10-03', '2027-10-04', '2027-10-05', '2027-10-06', '2027-10-07'],
        2028 => ['2028-01-01', '2028-01-26', '2028-01-27', '2028-01-28', '2028-01-29', '2028-01-30', '2028-01-31', '2028-04-04', '2028-05-01', '2028-05-28', '2028-10-01', '2028-10-02', '2028-10-03', '2028-10-04', '2028-10-05', '2028-10-06'],
        2029 => ['2029-01-01', '2029-02-13', '2029-02-14', '2029-02-15', '2029-02-16', '2029-02-17', '2029-02-18', '2029-04-05', '2029-05-01', '2029-06-16', '2029-09-22', '2029-10-01', '2029-10-02', '2029-10-03', '2029-10-04', '2029-10-05', '2029-10-06'],
        2030 => ['2030-01-01', '2030-02-03', '2030-02-04', '2030-02-05', '2030-02-06', '2030-02-07', '2030-02-08', '2030-04-05', '2030-05-01', '2030-06-05', '2030-09-12', '2030-10-01', '2030-10-02', '2030-10-03', '2030-10-04', '2030-10-05', '2030-10-06']
    ],
    'SG' => [  // Singapore
        2025 => ['2025-01-01', '2025-02-10', '2025-02-11', '2025-03-31', '2025-04-18', '2025-05-01', '2025-05-12', '2025-06-07', '2025-08-09', '2025-10-20', '2025-12-25'],
        2026 => ['2026-01-01', '2026-01-26', '2026-01-27', '2026-03-20', '2026-04-03', '2026-05-01', '2026-05-31', '2026-06-27', '2026-08-09', '2026-11-08', '2026-12-25'],
        2027 => ['2027-01-01', '2027-02-14', '2027-02-15', '2027-03-10', '2027-03-26', '2027-05-01', '2027-05-21', '2027-06-16', '2027-08-09', '2027-10-29', '2027-12-25'],
        2028 => ['2028-01-01', '2028-01-26', '2028-01-27', '2028-02-27', '2028-04-14', '2028-05-01', '2028-05-05', '2028-08-09', '2028-10-17', '2028-12-25'],
        2029 => ['2029-01-01', '2029-02-13', '2029-02-14', '2029-02-15', '2029-04-24', '2029-05-01', '2029-08-09', '2029-11-05', '2029-12-25'],
        2030 => ['2030-01-01', '2030-02-04', '2030-02-05', '2030-04-14', '2030-05-01', '2030-08-09', '2030-10-26', '2030-12-25']
    ],
    'LI' => [  // Liechtenstein
        2025 => ['2025-01-01', '2025-01-06', '2025-02-02', '2025-03-19', '2025-04-21', '2025-05-01', '2025-05-29', '2025-06-09', '2025-08-15', '2025-09-08', '2025-11-01', '2025-12-08', '2025-12-25', '2025-12-26'],
        2026 => ['2026-01-01', '2026-01-06', '2026-02-02', '2026-03-19', '2026-04-06', '2026-05-01', '2026-05-14', '2026-05-25', '2026-08-15', '2026-09-08', '2026-11-01', '2026-12-08', '2026-12-25', '2026-12-26'],
        2027 => ['2027-01-01', '2027-01-06', '2027-02-02', '2027-03-19', '2027-03-29', '2027-05-01', '2027-05-24', '2027-06-04', '2027-08-15', '2027-09-08', '2027-11-01', '2027-12-08', '2027-12-25', '2027-12-26'],
        2028 => ['2028-01-01', '2028-01-06', '2028-02-02', '2028-03-19', '2028-04-17', '2028-05-01', '2028-05-25', '2028-06-05', '2028-08-15', '2028-09-08', '2028-11-01', '2028-12-08', '2028-12-25', '2028-12-26'],
        2029 => ['2029-01-01', '2029-01-06', '2029-02-02', '2029-03-19', '2029-04-02', '2029-05-01', '2029-05-10', '2029-05-21', '2029-08-15', '2029-09-08', '2029-11-01', '2029-12-08', '2029-12-25', '2029-12-26'],
        2030 => ['2030-01-01', '2030-01-06', '2030-02-02', '2030-03-19', '2030-04-22', '2030-05-01', '2030-05-30', '2030-06-10', '2030-08-15', '2030-09-08', '2030-11-01', '2030-12-08', '2030-12-25', '2030-12-26']
    ],
    'US' => [  // United States (federal)
        2025 => ['2025-01-01', '2025-01-20', '2025-02-17', '2025-05-26', '2025-06-19', '2025-07-04', '2025-09-01', '2025-10-13', '2025-11-11', '2025-11-27', '2025-12-25'],
        2026 => ['2026-01-01', '2026-01-19', '2026-02-16', '2026-05-25', '2026-06-19', '2026-07-03', '2026-09-07', '2026-10-12', '2026-11-11', '2026-11-26', '2026-12-25'],
        2027 => ['2027-01-01', '2027-01-18', '2027-02-15', '2027-05-31', '2027-06-18', '2027-07-05', '2027-09-06', '2027-10-11', '2027-11-11', '2027-11-25', '2027-12-24', '2027-12-31'],
        2028 => ['2028-01-01', '2028-01-17', '2028-02-21', '2028-05-29', '2028-06-19', '2028-07-04', '2028-09-04', '2028-10-09', '2028-11-10', '2028-11-23', '2028-12-25'],
        2029 => ['2029-01-01', '2029-01-15', '2029-02-19', '2029-05-28', '2029-06-19', '2029-07-04', '2029-09-03', '2029-10-08', '2029-10-14', '2029-11-11', '2029-11-22', '2029-12-25'],
        2030 => ['2030-01-01', '2030-01-21', '2030-02-18', '2030-05-27', '2030-06-19', '2030-07-04', '2030-09-02', '2030-10-14', '2030-11-11', '2030-11-28', '2030-12-25']
    ]
];

// Country mapping from first char of C field
$countryMap = [
    'h' => 'HK',
    'c' => 'CN',
    's' => 'SG',
    'l' => 'LI',
    'u' => 'US'
];

// Current date for filter
$now = new DateTime('2025-10-05');

// Download CSV from GitHub
$url = 'https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/MATURITY.csv';
$csv_content = file_get_contents($url);
// Debug: echo '<!-- Debug: CSV length: ' . strlen($csv_content) . ' -->'; // Uncomment for debug
if ($csv_content === false) {
    die('Error fetching CSV');
}
$lines = explode("\n", $csv_content);

// Collect processed entries and footer lines
$entries = [];
$footer_lines = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    // Skip header and separator
    if (strpos($line, 'Matur') === 0 || strpos($line, '_____') === 0) {
        continue;
    }

    // Check if line starts with 5-digit date (YMMDD field, digits only)
    if (preg_match('/^(\d{5}),/', $line, $matches)) {
        $matur_str = $matches[1];
        // Debug: echo '<!-- Debug: Processing line: ' . $line . ' -->'; // Uncomment for debug

        // Parse YMMDD: units digit of year, MM (2), DD (2)
        $units = intval($matur_str[0]);
        $month_str = substr($matur_str, 1, 2);
        $day_str = substr($matur_str, 3, 2);
        $month = intval($month_str);
        $day = intval($day_str);
        $year = 2020 + $units;

        // Create DateTime
        $matur_date = DateTime::createFromFormat('Y-m-d', sprintf('%d-%s-%s', $year, $month_str, $day_str));
        if (!$matur_date) {
            continue; // Invalid date; skip
        }
        // Filter: only future maturities
        if ($matur_date <= $now) {
            continue;
        }
        // Debug: echo '<!-- Debug: Parsed date: ' . $matur_date->format('Y-m-d') . ' -->'; // Uncomment for debug

        // Parse parts with str_getcsv
        $parts = str_getcsv($line);
        if (count($parts) < 5) continue; // Min for C field (index 4)
        $c_field = isset($parts[4]) ? trim($parts[4]) : ''; // C field
        $country_code = $countryMap[ strtolower( substr($c_field, 0, 1) ) ] ?? 'US';
        // Debug: echo '<!-- Debug: Country: ' . $country_code . ' from C="' . $c_field . '" -->'; // Uncomment for debug (e.g., for 51028 line)

        // Add original entry
        $entries[] = [
            'date' => clone $matur_date,
            'line' => $line . "\n"
        ];

        // Calculate alert date: 3 business days before
        $alert_date = subtract_business_days($matur_date, 3, $country_code, $HOLIDAYS);
        // Debug: echo '<!-- Debug: Alert date: ' . $alert_date->format('Y-m-d') . ' -->'; // Uncomment for debug

        // Only add alert if future
        if ($alert_date > $now) {
            // Format alert as YMMDD: units digit, MM (2 w/0), DD (2 w/0)
            $alert_units = ($alert_date->format('Y') % 10);
            $alert_month_str = $alert_date->format('m');
            $alert_day_str = $alert_date->format('d');
            $alert_formatted = $alert_units . $alert_month_str . $alert_day_str;

            // Format matur as YMMDD for alert line
            $matur_units = ($matur_date->format('Y') % 10);
            $matur_month_str = $matur_date->format('m');
            $matur_day_str = $matur_date->format('d');
            $matur_formatted = $matur_units . $matur_month_str . $matur_day_str;

            // Create alert line
            $alert_line = $alert_formatted . ', Alert,     , → ,  ,' . $matur_formatted . "\n";

            $entries[] = [
                'date' => $alert_date,
                'line' => $alert_line
            ];
        }

    } elseif (strpos($line, '- ') === 0) {
        // Collect footer lines
        $footer_lines[] = $line . "\n";
    }
}

// Sort entries by date
usort($entries, function($a, $b) {
    return $a['date'] <=> $b['date'];
});
// Debug: echo '<!-- Debug: Total entries: ' . count($entries) . ' -->'; // Uncomment for debug

// Output
echo '<pre>
Matur, Bookd,  ¤n ,  ¤ , C, Details 
_____________________________________
';
foreach ($entries as $entry) {
    echo $entry['line'];
}
foreach ($footer_lines as $footer) {
    echo $footer;
}
echo '</pre>';

// Helper: Is business day?
function is_business_day(DateTime $date, $country, $holidays) {
    $weekday = $date->format('N'); // 1 Mon - 7 Sun
    if ($weekday >= 6) return false;

    $date_str = $date->format('Y-m-d');
    $year = $date->format('Y');
    $year_hols = $holidays[$country][$year] ?? [];
    return !in_array($date_str, $year_hols);
}

// Helper: Subtract business days
function subtract_business_days(DateTime $start, $days, $country, $holidays) {
    $current = clone $start;
    $count = 0;
    while ($count < $days) {
        $current->modify('-1 day');
        if (is_business_day($current, $country, $holidays)) {
            $count++;
        }
    }
    return $current;
}
?>
