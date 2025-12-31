<?php
// Set the year for Julian date conversion
$THIS_YEAR = 2025; 
// Convert Julian day to Gregorian date in YYMMDD format
function julianToGregorian($julianDay, $year) {
    $date = new DateTime("$year-01-01");
    $date->add(new DateInterval('P' . ($julianDay - 1) . 'D'));
    return $date->format('ymd');
}

// Abbreviate names (CHUN -> C, KYM -> K, else first letter)
function abbreviateName($name) {
    $name = strtoupper($name);
    if (strpos($name, 'CHUN') !== false) return 'C';
    if (strpos($name, 'KYM') !== false) return 'K';
    return substr($name, 0, 1);
}

// Process a single line of data
function processLine($line, $year) {
    $line = trim($line);
    
    // Skip empty lines
    if (empty($line)) return $line;
    
    // Default output is the original line
    $outputLine = $line;
    
    // Process lines starting with M1, M2, or __
    if (preg_match('/^(M1|M2|__)([a-zA-Z\/]+)\s+([A-Za-z0-9]+)\s+([a-zA-Z]{6,8})\s+([0-9]+)\s+(\d{3}[a-zA-Z0-9]+)/', $line, $matches)) {
        $name = $matches[2]; // e.g., MICHAEL/KYM
        $route = $matches[4]; // e.g., PVGHKGCX
        $flight = $matches[5]; // e.g., 0377
        $julianData = $matches[6]; // e.g., 226Y072K0220
        
        // Extract Julian day (first 3 digits)
        $julianDay = substr($julianData, 0, 3);
        $gregorianDate = julianToGregorian((int)$julianDay, $year);
        
        // Extract origin, destination, and airline from route
        $origin = substr($route, 0, 3);
        $destination = substr($route, 3, 3);
        $airline = (strlen($route) > 6) ? substr($route, 6, 2) : '';
        
        // Abbreviate name
        $nameParts = explode('/', $name);
        $lastName = end($nameParts);
        $abbreviatedName = abbreviateName($lastName);
        
        // Build output line: CA, CB, CC, CD, CE, CF, CG
        $outputLine = implode(', ', [
            $gregorianDate,    // CA: Gregorian date
            $abbreviatedName,  // CB: Abbreviated name
            $origin,           // CC: Origin airport
            $destination,      // CD: Destination airport
            $airline,          // CE: Airline code
            $flight,           // CF: Flight number
            $julianDay         // CG: Julian day
        ]);
    }
    
    return $outputLine;
}

// Process all data lines
function processData($data, $year) {
    $lines = explode("\n", $data);
    $output = [];
    
    foreach ($lines as $line) {
        $output[] = processLine($line, $year);
    }
    
    return $output;
}

$data = ' 
M1JI/TEST       TESTTT SYDHAKHU 0776 362I012G0058 147>3182OO5362BHU29880550036949802HU HU 3519793660 2PC
';

// Process the data
$output = processData($data, $THIS_YEAR);

// Output results (unchanged from original)
echo "<pre> Date ,Who Frm, To , By, Flgt, doy\n__________________________________";
foreach ($output as $line) {
    echo "$line\n";
}

echo "\nNote airline 'tr' is a train trip,
Airline code/custom name used for train stn.\n
The item 'K' = 'Kym MICHAEL,
The item 'C' = 'JI Chun' aka 'Sarah MICHAEL'.\n
The K and C normally travel together,\n
The 'Home Port' is Hong Kong.\n
Airports HKG, SZX, CAN are all used for
Hong Kong Home Port arrival and departure.\n
Total lines processed: " . count($output) . "</p>";

// Optional: Save processed data to CSV for use with your second dataset
function saveToCsv($output, $filename) {
    $file = fopen($filename, 'w');
    foreach ($output as $line) {
        // Only write lines that were processed (contain commas)
        if (strpos($line, ',') !== false) {
            fputcsv($file, explode(', ', $line));
        }
    }
    fclose($file);
}

// Uncomment the next line to save the processed data to a CSV file
saveToCsv($output, 'iata25out.csv');



// ATTEMPT TO ALSO SAVE A JSON RECORD

// Split into lines and clean up
$lines = array_filter(array_map('trim', explode("\n", $data)));

// Output array
$records = [];

foreach ($lines as $line) {
    // Skip empty lines and placeholder lines starting with "__"
    if (empty($line) === 0) {
        continue;
    }

    // Split by spaces, but preserve full remainder after 5th field
    $parts = preg_split('/\s+/', $line, 6); // Limit to 6 parts

    if (count($parts) < 5) {
        continue; // Skip malformed lines
    }

    // Extract first 5 fields
    $traveller           = trim($parts[0]);
    $ticket_no           = trim($parts[1]);
    $from_to_airline     = trim($parts[2]);
    $flight_no           = trim($parts[3]);
    $julian_date_class    = trim($parts[4]);
    $remainder           = isset($parts[5]) ? trim($parts[5]) : '';

    // Build record
    $records[] = [
        'Traveller'           => $traveller,
        'Ticket_N0'           => $ticket_no,
        'From_To_Airline'     => $from_to_airline,
        'Flight_No'           => $flight_no,
        'Julian_Date_Class_Other' => $julian_date_class,
        'Remainder_Of_Data'   => $remainder
    ];
}

// Save to JSON file in the same directory
$jsonFile = __DIR__ . '/iata25_Codes.json';
$jsonData = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($jsonFile, $jsonData) !== false) {
    echo "Successfully saved " . count($records) . " records to $jsonFile\n";
} else {
    echo "Failed to write JSON file.\n";
}




?>
