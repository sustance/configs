
<?php

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
$FLIGHT_CSV_FILE = 'processed_flight_data.csv'; // Output from previous script
$POINTS_CSV_FILE = 'ppoints.csv';

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
        // Skip empty rows
        if (empty($row) || count($row) < 6) {
            continue;
        }
        
        $date = trim($row[0]);
        $var = trim($row[1]);
        $who = trim($row[2]);
        $carrier = trim($row[3]);
        $flight = trim($row[4]);
        $points = trim($row[5]);
        
        // Use the date from the "var" column if available, otherwise use the first date column
        $effectiveDate = !empty($var) ? $var : $date;
        
        // Create a unique key for this points entry
        $key = "$who|$carrier|$flight|$effectiveDate";
        $pointsData[$key] = $points;
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
        // Skip empty rows
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
    $date = $flightRow[0]; // YYMMDD format
    $who = $flightRow[1];  // K or C
    $carrier = $flightRow[4]; // Airline code
    $flight = $flightRow[5]; // Flight number
    
    // Try to find exact match
    $key = "$who|$carrier|$flight|$date";
    if (isset($pointsData[$key])) {
        return $pointsData[$key];
    }
    
    // If no exact match, try without leading zeros in flight number
    $flightTrimmed = ltrim($flight, '0');
    if ($flightTrimmed !== $flight) {
        $key = "$who|$carrier|$flightTrimmed|$date";
        if (isset($pointsData[$key])) {
            return $pointsData[$key];
        }
    }
    
    return ''; // No match found
}

/**
 * Main execution
 */
try {
    // Load the data
    $pointsData = loadPointsData($POINTS_CSV_FILE);
    $flightData = loadFlightData($FLIGHT_CSV_FILE);
    
    // Output header
    echo "Date,Who,Origin,Destination,Carrier,Flight,JulianDay,Points\n";
    
    // Process each flight record
    foreach ($flightData as $flightRow) {
        // Find matching points
        $points = findMatchingPoints($flightRow, $pointsData);
        
        // Output the flight data with points column
        echo implode(',', $flightRow) . "," . $points . "\n";
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}

?>
