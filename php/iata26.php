<?php
// Shared on tilde/pubnix as they need useful script to attract more users/sponsors
// This is PHP as that is widely available on Pubnix server = free
// USE:
// Paste this file to public_html folder edit $THIS_YEAR and $data 
// - open with web browser, you have a neet trip summary
// - Cut and past to AI chat and ask questions "Total days in the EU during these trips" "Draw map these trips and coiies"
// PROCESS:
// Phone Scan barcode of every airline boarding pass. Many free apps and cammera con do this.
// Paste barcodes into "$data = ' " below in sequence when online next.
// - IATA barcode only has "day of year" of Julian dates. I have scriot for each year and set "$THIS_YEAR"
// - the first 5 standard IATA fields normally contain all the data i need. After that the IATA standard is not "standard"
// - In rare occasions you need to edit the space sepateted CSV of deviant of codes  
//  I also make entries for road/rail travel to cross borders or link air legs logically
// TODO:
// I could call an external csv file for data and append it from a web page etc but am rarely online at check-in.
// Consider just leapyear/non-leapyear or just tollerate leap years being 1 day out on the gregorian calendar.

// Set the year for Julian date conversion
 $THIS_YEAR = 2026; 
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
    if (preg_match('/^(M1|M2|__)([a-zA-Z\/]+)\s+([A-Za-z0-9]+)\s+([A-Za-z0-9]{6,8})\s+([A-Za-z0-9]+)\s+(\d{3}[a-zA-Z0-9]+)/', $line, $matches)) {
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
M1MICHAEL/KYM   EQZL7KL PVGHKGHX 0247 231T033H0079 100     
M1JI/CHUN       EQZL7KL PVGHKGHX 0247 231T033C0080 100                                                                    
                                                              
M1MICHAEL/KYM   ENJ4NGG SZXPVGZH 9521 225S014D0177 15D>8181OO6225BZH 2A479238953926500ZH LH 992000437391279  20KN*80600000K0900       
M1JI/CHUN       ENJ4NGG SZXPVGZH 9521 225S006B0176 15D>8182OO6225BZH 2A479238953926400ZH LH 992004719554828  20KN*80600000K0900       

__MICHAEL/KYM   xxxxxxx hkgszxdr 0000 224z 00
__JI/CHUN       xxxxxxx hkgszxdr 0000 224z 00


M1MICHAEL/KYM   E7TBYTM HELHKGAY 0099 205J006H0245 34A>5180 O6204BAY 2A10524943662870 AY Y8Y
M1JI/CHUN       E7TBYTM HELHKGAY 0099 205J006D0244 34A>5180 O6204BAY 2A10524943662860 AY Y8Y

M1MICHAEL/KYM   E7TBYTM LHRHELAY 1338 204J006A0315 34A>5180 O6204BAY 2A10524943662870 AY Y8Y
M1JI/CHUN       E7TBYTM LHRHELAY 1338 204J006D0314 34A>5180 O6204BAY 2A10524943662860 AY Y8Y
 
M1MICHAEL/KYM   EQWZXFE TFULHRCA 0423 192R016D0259 15C>7181OO6192BCA 29999242518752702CA LH 992000437391279 2PC*80600000K0901       
M1JI/CHUN       EQWZXFE TFULHRCA 2423 192R016H026# 15C>7182OO6192BCA 29999242518752602CA LH 992004719557528XQ2C*806#0000K0901       b

M1MICHAEL/KYM   EQWZXFE SINTFUCA 0404 191R002A0123 35C>7181OO6191BCA 399974693700129999242518752702CA LH 992000437391279 2PC*80601017K0911       
M1JI/CHUN       EQWZXFE SINTFUCA 0404 191R002C0119 35C>7182OO6191BCA 399901405400129999242518752602CA LH 992004719554828 2PC*80601020K0911

M1MICHAEL/KYM   EDD2OVK HKGSINCX 0711 188Y073E0227 34B>6180 K6188BCX 2A16048653569740 CX CX 1078165297 N
M1JI/CHUN       EDD2OVK HKGSINCX 0711 188Y073D0228 34B>6180 O6188BCX 2A16048653569730 CX CX 1022433862 N8AM

M1MICHAEL/KYM   EPFX0V1 HGHHKGHX 0113 171V032C0110 100
M1JI/CHUN       EPFX0V1 HGHHKGHX 0113 171V032H0111 100

__MICHAEL/KYM   xxxxxxx yixhghtr G3387 170z 00
__JI/CHUN       xxxxxxx yixhghtr G3387 170z 00

__MICHAEL/KYM   xxxxxxx hghyixtr G3326 168z 00
__JI/CHUN       xxxxxxx hghyixtr G3326 168z 00

M1MICHAEL/KYM   EMDJ29S PEKHGHHU 7677 164T038G0259 347>3180OD6164BHU 388050803300129880271327075600HU HU 3519793682 20K
M1JI/CHUN       EMDJ29S PEKHGHHU 7677 164T038H0260 147>3180OD6164BHU 29880271327075500HU HU 3519793660 20K

M1MICHAEL/KYM   EMF076Z SZXPEKHU 7710 160T057H0130 347>3181OO6160BHU 388047171800129880422733752200HU HU 3519793682 20K
M1JI/CHUN       EMF076Z SZXPEKHU 7710 160T057C0129 147>3181OO6160BHU 29880422733752100HU HU 3519793660 20K

__MICHAEL/KYM   xxxxxxx hkgszxrd 7041 159z 00
__JI/CHUN       xxxxxxx hkgszxrd 7041 159z 00


M1MICHAEL/KYM   EMT5H6F SHAHKGHX 0249 140Q033H0128 100
M1JI/CHUN       EMT5H6F SHAHKGHX 0249 140Q032C0129 100

__MICHAEL/KYM   0000000 szvshatr 7041 132x 
__JI/CHUN       0000000 szvshatr 7041 132x 

__MICHAEL/KYM   0000000 shaszvtr 7310 131x 
__JI/CHUN       0000000 shaszvtr 7310 131x 

M1MICHAEL/KYM   EMT5H6F HKGSHAHX 0238 131Q032H0191 147>3181OO6131BHX 29851211823845802HX HU 3519793682 1PC
M1JI/CHUN       EMT5H6F HKGSHAHX 0238 131Q032K0190 147>3182OO6131BHX 29851211823845702HX HU 3519793660 1PC


M1MICHAEL/KYM   EEAF6G5 SINHKGCX 0758 120Y050A0223 34B>6180 K6120BCX 2A16058934975000 CX CX 1078165297 N8AM
M1JI/CHUN       EEAF6G5 SINHKGCX 0758 120Y050C0222 34B>6180 K6120BCX 2A16058934974990 CX CX 1022433862 N8AM

M1MICHAEL/KYMMR ENE50V0 TFUSIN3U 3909 116E050C0086 100x
M1JI/CHUNMS     ENE50V0 TFUSIN3U 3909 116E050H0087 100x

M1MICHAEL/KYM   ENXD6CX WUHTFUMU 2651 113G057C0154 100x
M1JI/CHUN       ENXD6CX WUHTFUMU 2651 113G057L0153 100x

M1MICHAEL/KYM   EE8E8SY HKGWUHCX 0938 110Y075H0253 34B>6180 K6110BCX 2A16058935523080 CX CX 1078165297 N8AM
M1JI/CHUN       EE8E8SY HKGWUHCX 0938 110Y075G0254 34B>6180 K6110BCX 2A16058935523070 CX CX 1022433862 N8AM


M1MICHAEL/KYM   ENGHX56 HGHHKGCA 0727 099T036C0138 100p
M1JI/CHUN       ENGHX56 HGHHKGCA 0727 099T036J0135 100

__MICHAEL/KYM   xxxxxxx shahghtr C495 096z 00
__JI/CHUNMS     xxxxxxx shahghtr C495 096z 00

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

// === FREQUENT FLYER POINTS DATA ===
 $data_ffpoints = '
260819, K, fwc
260819, C, fwc

260813, K, m&m 20
260813, C, m&m 20

260812, K, x
260812, C, x

260724, K, am 4852
260724, C, am 

260723, K, am 1148
260723, C, 

260711, K, m&m 200
260711, C, m&m 200

260710, K, m&m 40
260710, C, m&m 40

260707, K, am 600
260707, C, 

260620, K, fwc
260620, C, fwc

260619, K, x
260619, C, x

260617, K, x
260617, C, x

260613, K, fwc
260613, C, fwc

260609, K, 
260609, C, fwc

260608, K, x
260608, C, x

260520, K, fwc
260520, C, fwc

260512, K, fwc x
260512, C, fwc x

260511, K, fwc x
260511, C, fwc x

260430, K, am 600 
260430, C, 

260423, K, del 207
260423, C, 

260420, K, am 300
260420, C,

260409, K, m&m 20
260409, C, m&m 20

260409, K, m&m 20
260409, C, m&m 20

260330, K, m&m 20
260330, C, m&m 20

260305, K, m&m 20
260305, C, m&m 20

260302, K, m&m 20
260302, C, m&m 20

260124, K, m&m 20
260124, C, m&m 20

260121, K, m&m 20
260121, C, m&m 20

260114, K, am 600
260114, C, 

250107, K, del 147 
250107, K, 
';

// === PARSE FREQUENT FLYER POINTS INTO LOOKUP ARRAY ===
 $ff_points = [];
 $ff_lines = explode("\n", trim($data_ffpoints));
foreach ($ff_lines as $ff_line) {
    $ff_line = trim($ff_line);
    if (empty($ff_line)) continue;
    
    $parts = array_map('trim', explode(',', $ff_line));
    if (count($parts) >= 2) {
        $date = $parts[0];
        $person = $parts[1];
        $marker = isset($parts[2]) ? trim($parts[2]) : '';
        
        $key = $date . '_' . $person;
        if (!empty($marker)) {
            $ff_points[$key] = $marker;
        }
    }
}

// === PROCESS THE DATA ===
 $output = processData($data, $THIS_YEAR);

// === ADD FF POINTS MARKER COLUMN ===
 $output_with_ff = [];
foreach ($output as $line) {
    if (strpos($line, ',') !== false) {
        $parts = array_map('trim', explode(',', $line));
        if (count($parts) >= 2) {
            $date = $parts[0];
            $person = $parts[1];
            $key = $date . '_' . $person;
            
            $marker = isset($ff_points[$key]) ? $ff_points[$key] : '';
            $output_with_ff[] = $line . ', ' . $marker;
        } else {
            $output_with_ff[] = $line;
        }
    } else {
        $output_with_ff[] = $line;
    }
}

// === OUTPUT RESULTS ===
echo "
_** 2026 TRAVEL SUMMARY**_

** CHINA DAYS **
```
Start ,Days
------, --- 
260812,  8
260710,  2
260608, 13
260511, 10
260420,  7
260327, 14
260305,  6
260205,  7
260107,  8
```
  **Total 75** ytd
  
**TRIPS & POINTS**
```
Date ,Who,Frm, To , By, Flgt, doy, FFpt\n__________________________________";
foreach ($output_with_ff as $line) {
    echo "$line\n";
}

echo "```
_Notes_
- airline 'tr' is train, 'rd' is road
- Airline code/custom name used for train stn
- The item 'K' = 'K M, 'C' = 'J C' aka 'S M'
- K and C travel together, 'Home Port' is HKG
- FFpt column: ff if credited, blank = missed
- Total lines processed: " . count($output_with_ff);

// === SAVE TO CSV ===
function saveToCsv($output, $filename) {
    $file = fopen($filename, 'w');
    foreach ($output as $line) {
        if (strpos($line, ',') !== false) {
            fputcsv($file, array_map('trim', explode(',', $line)));
        }
    }
    fclose($file);
}

 $filename = 'iata_' . $THIS_YEAR . 'out.csv'; 
saveToCsv($output_with_ff, $filename);

// === ATTEMPT TO ALSO SAVE A JSON RECORD ===

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
 $jsonFile = __DIR__ . '/iata26_Codes.json';
 $jsonData = json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

if (file_put_contents($jsonFile, $jsonData) !== false) {
    echo "\nSuccessfully saved " . count($records) . " records to $jsonFile\n";
} else {
    echo "\nFailed to write JSON file.\n";
}

?>
