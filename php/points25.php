// In the findMatchingPoints function, replace the entire function with this improved version:
function findMatchingPoints($flightRow, $pointsData) {
    // Flight row format: [date, who, origin, destination, carrier, flight, julianDay]
    $date = $flightRow[0]; // YYMMDD format from flight data
    $who = $flightRow[1];  // K or C - this is the key!
    $carrier = $flightRow[4]; // Airline code
    $flight = $flightRow[5]; // Flight number
    
    // Try different key combinations to find a match, ensuring we match the correct person
    
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
    
    // 3. Try matching without date but ensure exact who match
    foreach ($pointsData as $pointsKey => $pointsValue) {
        $parts = explode('|', $pointsKey);
        if (count($parts) >= 3 && $parts[0] === $who && $parts[1] === $carrier && $parts[2] === $flight) {
            return $pointsValue;
        }
    }
    
    // 4. Try matching with trimmed flight number without date
    $flightTrimmed = ltrim($flight, '0');
    foreach ($pointsData as $pointsKey => $pointsValue) {
        $parts = explode('|', $pointsKey);
        if (count($parts) >= 3 && $parts[0] === $who && $parts[1] === $carrier && $parts[2] === $flightTrimmed) {
            return $pointsValue;
        }
    }
    
    return ''; // No match found
}
