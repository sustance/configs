<?php
// Set the year for Julian date conversion
$THIS_YEAR = 2026; 
// Convert Julian day to Gregorian date in YYMMDD format
function julianToGregorian($julianDay, $year) {
    $date = new DateTime("$year-01-01");
    $date->add(new DateInterval('P' . ($julianDay - 1) . 'D'));
add:     return $date->format('ymd');
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
    if (preg_match('/^(M1|M2|__)([a-zA-Z\/]+)\s+([A-Za-z0-9]+)\s+([a-zA-Z]{6,8})\s+([A-Za-z0-9]+)\s+(\d{3}[a-zA-Z0-9]+)/', $line, $matches)) {
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

M1MICHAEL/KYM   ENGHX56 HGHHKGCA 0727 099T036C0138 100p!
M1JI/CHUN       ENGHX56 HGHHKGCA 0727 099T036J0135 100

__MICHAEL/KYM   xxxxxxx shahghtr C495 092z 00
__JI/CHUNMS     xxxxxxx shahghtr C495 092z 00

M1MICHAEL/KYM   ENXWZ8J CANSHACA 1829 089L032E0255 100
M1JI/CHUN       ENXWZ8J CANSHACA 1829 089L032H0254 100

__MICHAEL/KYM   xxxxxxx hkgcantr 6536 086z 00
__JI/CHUNMS     xxxxxxx hkgcantr 6536 086z 00


__MICHAEL/KYM   999999 xmnhkgtr G923 069TRN00000
__JI/CHUNMS     999999 xmnhkgtr G923 069TRN00000

__MICHAEL/KYM   999999 szxxmntr D668 066TRN00000
__JI/CHUNMS     999999 szxxmntr D668 066TRN00000

M1MICHAEL/KYM   EPLNVM5 SINSZXZH 0240 064T022E0120 15C>3181OO6064BZH 29479480938118102ZH LH 992000437391279  2PC*30600000K0911       
M1JI/CHUN       EPLNVM5 SINSZXZH 0240 064T022F0117 15C>3182OO6064BZH 29479480938118002ZH LH 992004719554828  2PC*30600000K0911       

M1MICHAEL/KYM   EEZZEKQ HKGSINSQ 0893 061Y060C0164 37E>832011:GAGB1$- 06185613830010541287386001 TV 2A61824738523950 SQ LH 992000437391279@ N,80602028K09
M1JI/CHUN       EEZZEAA HKGSINSQ 0893 061Y060D0163 37E>8320 O6061BSQ 06185613830010618561386001 2A61824738523960 SQ LH 992165099554828 N*80602028K09

M1MICHAEL/KYM   EQHYX2S FUKHKGHX 0639 042O033C0113 147>3181OO6042BHX 29851230871941602HX HU 3519793682 1PC
M1JI/CHUN       EQHYX2S FUKHKGHX 0639 042O033H0112 147>3181OO6042BHX 29851230871941502HX HU 3519793660 1PC

M1MICHAEL/KYM   EQHYX2S HKGFUKHX 0640 036Q033C0146 147>3181OO6036BHX 29851230871941602HX HU 3519793682 1PC
M1JI/CHUN       EQHYX2S HKGFUKHX 0640 036Q033H0147 147>3182OO6036BHX 29851230871941502HX HU 3519793660 1PC

M1MICHAEL/KYM   EE9KWAR SINHKGSQ 0898 024Y056H0056 37E>8320 K6024BSQ 2A61824725083540 SQ LH 992225022757127 N*80601000K09 LHS           
M1JI/CHUN       EE9KWAR SINHKGSQ 0898 024Y056G0055 37E>8320 K6024BSQ 2A61824725083550 SQ LH 992227673319868 N*80601000K09 LHS           

M1MICHAEL/KYM   EE9KWAR HKGSINSQ 0875 021Y068C0116 37E>8320=5:GACB1$ 2A61824725083540 SQ LH 986976222757127 N*80600000K09 LHS
M1JI/CHUN       EE9KWAR HKGSINSQ 0875 021Y068D0115 37E>8320 O6021BSQ 2A61824725083550 SQ LH 992227673319868 N*80600000K09 LHS         

M1MICHAEL/KYM   ED98KBZ PVGHKGCX 0377 014Y060H0019 34B>6180 O6014BCX 2A16021285298900 CX CX 1078165297 N8AM
M1JI/CHUN       ED963CT PVGHKGCX 0377 014Y060K0020 34B>6180 O6014BCX 2A16021285298870 CX CX 1022433862 N8AM

M1MICHAEL/KYMMR EPHQMW3 TPEPVGMU 5006 007Z038D0139 100
M1JI/CHUNMS     EPHQMW3 TPEPVGMU 5006 007Z037D0138 100

M1MICHAEL/KYMMR EQZTEY3 HKGTPEHX 0260 005O050H0007 147>3181WW6003BHX 29851604682582502HX 1PC
M1JI/CHUNMS     EQZTEY3 HKGTPEHX 0260 005O050C0006 147>3182WW6003BHX 29851604682582402HX 1PC
';

// Process the data
$output = processData($data, $THIS_YEAR);

// Output results (unchanged from original)
echo "<pre> Date ,Who,Frm, To , By, Flgt, doy\n__________________________________";
foreach ($output as $line) {
    echo "$line\n";
}

echo "\nNote airline 'tr' is a train trip,
Airline code/custom name used for train stn.\n
The item 'K' = 'K M,
The item 'C' = 'J C' aka 'S M'.\n
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
