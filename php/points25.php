<php

/**
 * Script to combine flight data with points information
 * 
 * Reads two CSV files:
 * 1. Processed flight data (from previous script)
 * 2. Points data (ppoints.csv)
 * 
 * Outputs the flight data with an additional column showing points if available
 */

// Configuration
$FLIGHT_CSV_FILE = 'iata25out.csv'; // Output from previous script
$POINTS_CSV_FILE = 'pts-paid.csv';

/**
 * Load and parse points data from CSV file
 * 
 * @param string $filename Path to the CSV file
 * @return array Associative array of points data keyed by who+carrier+flight+date
 */
function loadPointsData($filename) {
    $pointsData = [];
    
    if (!file_exists($filename)) {
        die("Error: Points file '$filename' not found.\n");
    }
    
    $handle = fopen($filename, 'r');
    if (!$handle) {
        die("Error: Could not open points file '$filename'.\n");
    }
    
    // Skip header row
    fgetcsv($handle);
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        // Skip empty rows or rows that don't have enough data
        if (empty($row) || count($row) < 6 || trim($row[0]) === '') {
            continue;
        }
        
        $date = trim($row[0]);
        $who = trim($row[2]);
        $carrier = trim($row[3]);
        $flight = trim($row[4]);
        $points = trim($row[5]);
        
        // Skip header rows or invalid data
        if ($date === 'date' || $who === 'who' || empty($who) || empty($carrier)) {
            continue;
        }
        
        // Create a unique key for this points entry (using only the first date column)
        $key = "$who|$carrier|$flight|$date";
        $pointsData[$key] = $points;
        
        // Also create a key with flight number without leading zeros
        $flightTrimmed = ltrim($flight, '0');
        if ($flightTrimmed !== $flight) {
            $keyAlt = "$who|$carrier|$flightTrimmed|$date";
            $pointsData[$keyAlt] = $points;
        }
    }
    
    fclose($handle);
    return $pointsData;
}

/**
 * Load flight data from CSV file
 * 
 * @param string $filename Path to the CSV file
 * @return array Array of flight data rows
 */
function loadFlightData($filename) {
    $flightData = [];
    
    if (!file_exists($filename)) {
        die("Error: Flight data file '$filename' not found.\n");
    }
    
    $handle = fopen($filename, 'r');
    if (!$handle) {
        die("Error: Could not open flight data file '$filename'.\n");
    }
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        // Skip empty rows or rows that don't have enough data
        if (empty($row) || count($row) < 7) {
            continue;
        }
        $flightData[] = $row;
    }
    
    fclose($handle);
    return $flightData;
}

/**
 * Find matching points for a flight record
 * 
 * @param array $flightRow Flight data row
 * @param array $pointsData Points data associative array
 * @return string Points value or empty string if no match
 */
function findMatchingPoints($flightRow, $pointsData) {
    // Flight row format: [date, who, origin, destination, carrier, flight, julianDay]
    $date = $flightRow[0]; // YYMMDD format from flight data
    $who = $flightRow[1];  // K or C
    $carrier = $flightRow[4]; // Airline code
    $flight = $flightRow[5]; // Flight number
    
    // Try different key combinations to find a match
    
    // 1. Exact match with flight number as-is
    $key = "$who|$carrier|$flight|$date";
    if (isset($pointsData[$key])) {
        return $pointsData[$key];
    }
    
    // 2. Match with flight number without leading zeros
    $flightTrimmed = ltrim($flight, '0');
    if ($flightTrimmed !== $flight) {
        $key = "$who|$carrier|$flightTrimmed|$date";
        if (isset($pointsData[$key])) {
            return $pointsData[$key];
        }
    }
    
    // 3. Try matching without date (if points data might have different date format)
    $key = "$who|$carrier|$flight";
    foreach ($pointsData as $pointsKey => $pointsValue) {
        if (strpos($pointsKey, $key) === 0) {
            return $pointsValue;
        }
    }
    
    // 4. Try matching with just carrier and flight (most basic match)
    $key = "$carrier|$flight";
    foreach ($pointsData as $pointsKey => $pointsValue) {
        if (strpos($pointsKey, $key) !== false && strpos($pointsKey, $who) !== false) {
            return $pointsValue;
        }
    }
    
    return ''; // No match found
}

/**
 * Debug function to output what matching is being attempted
 */
function debugMatching($flightRow, $pointsData) {
    $date = $flightRow[0];
    $who = $flightRow[1];
    $carrier = $flightRow[4];
    $flight = $flightRow[5];
    
    echo "Trying to match: Date=$date, Who=$who, Carrier=$carrier, Flight=$flight\n";
    
    $flightTrimmed = ltrim($flight, '0');
    
    echo "Looking for keys:\n";
    echo "1. $who|$carrier|$flight|$date\n";
    echo "2. $who|$carrier|$flightTrimmed|$date\n";
    
    $found = false;
    foreach ($pointsData as $key => $value) {
        if (strpos($key, $who) !== false && strpos($key, $carrier) !== false) {
            echo "Potential match: $key => $value\n";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "No potential matches found in points data\n";
    }
    
    echo "-----------------\n";
}

/**
 * Main execution
 */
try {
    // Load the data
    $pointsData = loadPointsData($POINTS_CSV_FILE);
    $flightData = loadFlightData($FLIGHT_CSV_FILE);
    
    // For debugging: output what's in the points data
    echo "Points data loaded:\n";
    foreach ($pointsData as $key => $value) {
        echo "$key => $value\n";
    }
    echo "-----------------\n";
    
    // Output header
    echo "<pre>Date,Who,Origin,Destination,Carrier,Flight,JulianDay,Points\n";
    
    // Process each flight record
    foreach ($flightData as $flightRow) {
        // For debugging: uncomment the next line to see matching attempts
        // debugMatching($flightRow, $pointsData);
        
        // Find matching points
        $points = findMatchingPoints($flightRow, $pointsData);
        
        // Output the flight data with points column
        echo implode(',', $flightRow) . "," . $points . "\n";
    }
    echo "</pre>"
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

?>
