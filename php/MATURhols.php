<?php
// Telegram bot token (replace with your bot token)
 $BOT_TOKEN = "1017229657:AAFWCB0Uvr3MpP19LSTVTx203bh9LvH_WnY";

// PID variable
 $PID = "993903069"; //telWassup

$server = getenv('C_ID');
 


// --- 1. DEFINE YOUR HOLIDAY DATA ---
 $HOLIDAYS = [
    'HK' => [  // Hong Kong
        2025 => ['2025-01-01', '2025-01-29', '2025-01-30', '2025-01-31', '2025-04-04', '2025-04-18', '2025-04-19', '2025-04-21', '2025-05-01', '2025-05-05', '2025-05-31', '2025-07-01', '2025-10-01', '2025-10-02', '2025-10-03', '2025-10-29', '2025-12-25', '2025-12-26'],
    ],
    'CN' => [  // China
        2025 => ['2025-01-01', '2025-01-28', '2025-01-29', '2025-01-30', '2025-01-31', '2025-04-04', '2025-04-05', '2025-05-01', '2025-05-02', '2025-05-03', '2025-05-04', '2025-05-31', '2025-09-15', '2025-10-01', '2025-10-02', '2025-10-03', '2025-10-04', '2025-10-05', '2025-10-06', '2025-10-07'],
    ],
    'SG' => [  // Singapore
        2025 => ['2025-01-01', '2025-02-10', '2025-02-11', '2025-03-31', '2025-04-18', '2025-05-01', '2025-05-12', '2025-06-07', '2025-08-09', '2025-10-20', '2025-12-25'],
    ],
    'LI' => [  // Liechtenstein
        2025 => ['2025-01-01', '2025-01-06', '2025-02-02', '2025-03-19', '2025-04-21', '2025-05-01', '2025-05-29', '2025-06-09', '2025-08-15', '2025-09-08', '2025-11-01', '2025-12-08', '2025-12-25', '2025-12-26'],
    ],
    'US' => [  // United States (federal)
        2025 => ['2025-01-01', '2025-01-20', '2025-02-17', '2025-05-26', '2025-06-19', '2025-07-04', '2025-09-01', '2025-10-13', '2025-11-11', '2025-11-27', '2025-12-25'],
    ]
];

// --- 2. SET THE DATE RANGE ---
 $now = new DateTime();
 $end = (new DateTime())->modify('+1 month');

// --- 3. COMPUTE THE HOLIDAYS WITHIN THE RANGE ---
// This is the new logic that was missing.
 $computedHolidays = [];
 $currentYear = (int)$now->format('Y');

foreach ($HOLIDAYS as $countryCode => $yearlyData) {
    // Check if we have data for the current year for this country
    if (isset($yearlyData[$currentYear])) {
        foreach ($yearlyData[$currentYear] as $dateString) {
            $holidayDate = new DateTime($dateString);
            // Check if the holiday is within our desired range
            if ($holidayDate >= $now && $holidayDate <= $end) {
                $computedHolidays[] = [
                    'date' => $dateString,
                    'country' => $countryCode
                ];
            }
        }
    }
}

// Sort the computed holidays by date for a clean output
usort($computedHolidays, function ($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});


// --- 4. GENERATE THE OUTPUT USING THE COMPUTED DATA ---
ob_start(); // Start output buffering

echo "<pre>\nPublic holidays by country from " . $now->format('Y-m-d') . " to " . $end->format('Y-m-d') . " via " . $server . ":\n\n";

// FIX: Loop over the new $computedHolidays array, not the old placeholder.
foreach ($computedHolidays as $holiday) {
    echo "{$holiday['date']} - {$holiday['country']}\n";
}

echo "</pre>";

 $output = ob_get_clean(); // Get buffered output

// Strip HTML <pre> tags and trim to send plain text with markdown style code block
 $messageText = "```\n" . strip_tags($output) . "\n```";

// Function to send message via Telegram Bot API
function sendTelegramMessage($chatId, $message, $token) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ];

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true
    ];

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

// Send the holidays list to Telegram PID
sendTelegramMessage($PID, $messageText, $BOT_TOKEN);
?>
