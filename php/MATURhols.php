<?php
// Simple PHP script to generate a list of holidays by country by day for the next month from date of run
// Outputs in <pre> for monospace readability on mobile (narrow columns, no wrap)
// No data validation; assumes well-formed input

$HOLIDAYS = [
    'HK' => [  // Hong Kong
        2025 => ['2025-01-01', '2025-01-29', '2025-01-30', '2025-01-31', '2025-04-04', '2025-04-18', '2025-04-19', '2025-04-21', '2025-05-01', '2025-05-05', '2025-05-31', '2025-07-01', '2025-10-01', '2025-10-02', '2025-10-03', '2025-10-29', '2025-12-25', '2025-12-26'],
        // Add more years if needed...
    ],
    'CN' => [  // China
        2025 => ['2025-01-01', '2025-01-28', '2025-01-29', '2025-01-30', '2025-01-31', '2025-04-04', '2025-04-05', '2025-05-01', '2025-05-02', '2025-05-03', '2025-05-04', '2025-05-31', '2025-09-15', '2025-10-01', '2025-10-02', '2025-10-03', '2025-10-04', '2025-10-05', '2025-10-06', '2025-10-07'],
        // Add more years if needed...
    ],
    'SG' => [  // Singapore
        2025 => ['2025-01-01', '2025-02-10', '2025-02-11', '2025-03-31', '2025-04-18', '2025-05-01', '2025-05-12', '2025-06-07', '2025-08-09', '2025-10-20', '2025-12-25'],
        // Add more years if needed...
    ],
    'LI' => [  // Liechtenstein
        2025 => ['2025-01-01', '2025-01-06', '2025-02-02', '2025-03-19', '2025-04-21', '2025-05-01', '2025-05-29', '2025-06-09', '2025-08-15', '2025-09-08', '2025-11-01', '2025-12-08', '2025-12-25', '2025-12-26'],
        // Add more years if needed...
    ],
    'US' => [  // United States (federal)
        2025 => ['2025-01-01', '2025-01-20', '2025-02-17', '2025-05-26', '2025-06-19', '2025-07-04', '2025-09-01', '2025-10-13', '2025-11-11', '2025-11-27', '2025-12-25'],
        // Add more years if needed...
    ]
];

$countryMap = ['HK', 'CN', 'SG', 'LI', 'US'];

// Current date and end date (one month later)
$now = new DateTime();  // current date
$end = (clone $now)->modify('+1 month');

// Collect holidays within the next month from $HOLIDAYS
$holidaysNextMonth = [];

// Iterate countries
foreach ($countryMap as $country) {
    if (!isset($HOLIDAYS[$country])) {
        continue;
    }
    $year = (int)$now->format('Y');
    // Check holidays for current and possibly next year (if date range crosses year end)
    $yearsToCheck = [$year];
    if ($now->format('m') == 12) {
        $yearsToCheck[] = $year + 1;
    }
    foreach ($yearsToCheck as $y) {
        if (!isset($HOLIDAYS[$country][$y])) {
            continue;
        }
        foreach ($HOLIDAYS[$country][$y] as $holidayDateStr) {
            $holidayDate = DateTime::createFromFormat('Y-m-d', $holidayDateStr);
            if ($holidayDate >= $now && $holidayDate <= $end) {
                $holidaysNextMonth[] = [
                    'country' => $country,
                    'date' => $holidayDateStr,
                ];
            }
        }
    }
}

// Sort holidays by date ascending, then by country code
usort($holidaysNextMonth, function($a, $b) {
    if ($a['date'] === $b['date']) {
        return strcmp($a['country'], $b['country']);
    }
    return strcmp($a['date'], $b['date']);
});

// Output in <pre> block
echo "<pre>\nPublic holidays by country from " . $now->format('Y-m-d') . " to " . $end->format('Y-m-d') . " via " . $C_ID . ":\n\n";

foreach ($holidaysNextMonth as $holiday) {
    echo "{$holiday['date']} - {$holiday['country']}\n";
}

echo "</pre>";
?>
