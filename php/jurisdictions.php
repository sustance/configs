<?php
/**
 * Personal Travel Log Processor
 * ================================
 * Processes boarding pass scan data from iata_2026out.csv to produce:
 *   - Person C: Jurisdiction day counts (total + working days) for tax/work-limit reporting
 *   - Person K: HKG-only day count for HK social security reporting
 *
 * Stage 2 (button-triggered): DeepSeek API query on entry requirements for K's visited jurisdictions.
 *
 * Expects files in the same directory:
 *   ./iata_2026out.csv   — travel log (no header, see column map below)
 *   ./airports.csv       — ourairports.com full airport list
 */
 
// ---------------------------------------------------------------------------
// CONFIG
// ---------------------------------------------------------------------------
 
define('TRAVEL_CSV',   __DIR__ . '/iata_2026out.csv');
define('AIRPORTS_CSV', __DIR__ . '/airports.csv');
define('DEEPSEEK_KEY', 'dummykey');
define('DEEPSEEK_URL', 'https://api.deepseek.com/v1/chat/completions');
 
// Column indices in iata_2026out.csv (0-based, no header row)
const COL_DATE     = 0;   // YYMMDD
const COL_PERSON   = 1;   // C or K
const COL_FROM     = 2;   // Origin IATA
const COL_TO       = 3;   // Destination IATA
const COL_AIRLINE  = 4;
const COL_FLIGHT   = 5;
const COL_JULIAN   = 6;   // Julian day of year — used as secondary sort key
const COL_SKIP     = 7;   // Y = exclude this row from day-count calculations
 
// Warning threshold: print notice if total days in a jurisdiction exceeds this
const C_WARNING_DAYS = 90;
 
// ---------------------------------------------------------------------------
// BOOTSTRAP — load data, run Stage 1, render HTML
// ---------------------------------------------------------------------------
 
$errors   = [];
$results  = [];
 
try {
    $airports = loadAirports(AIRPORTS_CSV);
    $flights  = loadFlights(TRAVEL_CSV);
 
    $results['C'] = processPerson('C', $flights, $airports);
    $results['K'] = processPersonK('K', $flights, $airports);
 
} catch (RuntimeException $e) {
    $errors[] = $e->getMessage();
}
 
// Stage 2: DeepSeek call is POST-triggered from the button
$deepseekResponse = null;
$deepseekError    = null;
 
if (isset($_POST['run_deepseek']) && empty($errors)) {
    try {
        [$deepseekResponse, $deepseekError] = queryDeepSeek($results['K']['jurisdictions_visited'] ?? []);
    } catch (RuntimeException $e) {
        $deepseekError = $e->getMessage();
    }
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — DATA LOADING
// ---------------------------------------------------------------------------
 
/**
 * Load airports.csv into a lookup array keyed by IATA code.
 * Only large_airport and medium_airport types are indexed to avoid
 * false matches with closed or helipad facilities sharing an IATA code.
 * Returns: [ 'HKG' => 'HK', 'LHR' => 'GB', ... ]
 */
function loadAirports(string $path): array {
    if (!file_exists($path)) halt("airports.csv not found at: $path");
 
    $handle = fopen($path, 'r');
    $header = fgetcsv($handle);   // ourairports.com has a header row
 
    // Map header names to column indices — file format may vary slightly
    $idx = array_flip($header);
    $iataCol    = $idx['iata_code']   ?? null;
    $typeCol    = $idx['type']        ?? null;
    $countryCol = $idx['iso_country'] ?? null;
 
    if ($iataCol === null || $typeCol === null || $countryCol === null) {
        halt("airports.csv is missing expected columns: iata_code, type, iso_country");
    }
 
    $lookup = [];
    while (($row = fgetcsv($handle)) !== false) {
        $type = trim($row[$typeCol] ?? '');
        if (!in_array($type, ['large_airport', 'medium_airport'], true)) continue;
 
        $iata    = trim($row[$iataCol]    ?? '');
        $country = trim($row[$countryCol] ?? '');
        if ($iata === '' || $country === '') continue;
 
        $lookup[$iata] = $country;
    }
    fclose($handle);
 
    return $lookup;
}
 
/**
 * Load and sort iata_2026out.csv.
 * Rows are returned sorted ascending by Date (col 0) then Julian day (col 6).
 * Raw file is in reverse chronological order, so sorting is always required.
 */
function loadFlights(string $path): array {
    if (!file_exists($path)) halt("iata_2026out.csv not found at: $path");
 
    $rows = [];
    $handle = fopen($path, 'r');
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 7) continue;  // skip malformed/blank lines
        $rows[] = $row;
    }
    fclose($handle);
 
    // Sort ascending: primary = date string (YYMMDD sorts correctly as string),
    // secondary = Julian day as integer
    usort($rows, function($a, $b) {
        $dateCmp = strcmp($a[COL_DATE], $b[COL_DATE]);
        if ($dateCmp !== 0) return $dateCmp;
        return (int)$a[COL_JULIAN] <=> (int)$b[COL_JULIAN];
    });
 
    return $rows;
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — PERSON C PROCESSING
// ---------------------------------------------------------------------------
 
/**
 * Process all flights for a given person and return jurisdiction day summaries.
 *
 * Strategy:
 *   1. Filter rows for this person, collapse same-day multi-leg sequences into
 *      a single logical movement (first origin → final destination only).
 *   2. Walk the collapsed movements in order, tracking current location and
 *      the date the person arrived there.
 *   3. When a departure from a location is seen, close out that stay and
 *      calculate total/working days for that jurisdiction span.
 *   4. Handle year-start and year-end exceptions.
 */
function processPerson(string $person, array $allFlights, array $airports): array {
 
    $flights = filterAndCollapse($person, $allFlights);
 
    // We build a list of stays: [ ['country'=>'HK', 'from'=>DateTime, 'to'=>DateTime], ... ]
    // from/to are the inclusive first and last calendar dates in that jurisdiction.
    $stays = [];
 
    // currentLocation: where the person is right now as we walk forward
    // arrivedOn: the date they arrived at currentLocation
    $currentLocation = null;
    $arrivedOn       = null;
    $firstFlight     = true;
 
    foreach ($flights as $f) {
        $date    = parseDate($f[COL_DATE]);
        $from    = $f[COL_FROM];
        $to      = $f[COL_TO];
        $fromISO = resolveIATA($from, $airports, $f[COL_DATE], $person);
        $toISO   = resolveIATA($to,   $airports, $f[COL_DATE], $person);
 
        if ($firstFlight) {
            // Year-start exception: accept without continuity check.
            // Person is treated as having been at $from since Jan 1.
            $yearStart = new DateTime("20{$f[COL_DATE][0]}{$f[COL_DATE][1]}-01-01");
            $arrivedOn       = $yearStart;
            $currentLocation = $fromISO;
            $firstFlight     = false;
        }
 
        // Continuity check: their current known location must match this flight's origin
        if ($currentLocation !== $fromISO) {
            halt(
                "Continuity error for $person on {$f[COL_DATE]}: " .
                "expected to be in $currentLocation but flight departs from $fromISO ($from). " .
                "Please review iata_2026out.csv."
            );
        }
 
        // Close out the stay in $currentLocation: they were there from $arrivedOn to $date (departure day)
        $stays[] = [
            'country' => $currentLocation,
            'from'    => clone $arrivedOn,
            'to'      => clone $date,
        ];
 
        // Move to destination
        $currentLocation = $toISO;
        $arrivedOn       = clone $date;
    }
 
    // Year-end exception: person remains at $currentLocation through Dec 31
    if ($currentLocation !== null) {
        $lastDate  = $arrivedOn;
        $yearStr   = '20' . substr(array_values(array_filter(
            $allFlights,
            fn($r) => trim($r[COL_PERSON]) === $person
        ))[0][COL_DATE] ?? '260101', 0, 2);
        // Derive year from the last flight date we processed
        $yearEnd = new DateTime($lastDate->format('Y') . '-12-31');
        $stays[] = [
            'country' => $currentLocation,
            'from'    => clone $arrivedOn,
            'to'      => $yearEnd,
        ];
    }
 
    // Aggregate stays by country
    return aggregateStaysC($stays, $person);
}
 
/**
 * Filter flights for a person, remove skip-flagged rows from day counts,
 * then collapse same-day multi-leg journeys:
 *   HKG→LHR→CDG on the same day becomes a single record HKG→CDG.
 *
 * Skip-flagged rows are still read (kept in the pre-collapse sequence for
 * continuity) but their intermediate airports are never surfaced as
 * independent jurisdiction entries.
 *
 * Returns an array of simplified flight rows with the same column structure.
 */
function filterAndCollapse(string $person, array $allFlights): array {
    // Keep only this person's rows
    $mine = array_values(array_filter(
        $allFlights,
        fn($r) => trim($r[COL_PERSON]) === $person
    ));
 
    if (empty($mine)) halt("No flight records found for person '$person'.");
 
    // Group by date
    $byDate = [];
    foreach ($mine as $row) {
        $byDate[$row[COL_DATE]][] = $row;
    }
 
    $collapsed = [];
    foreach ($byDate as $date => $dayRows) {
        if (count($dayRows) === 1) {
            // Single flight day — use as-is (skip flag still respected below)
            $collapsed[] = $dayRows[0];
        } else {
            // Multi-leg day: first origin → final destination
            // Rows are already sorted by Julian day within the date
            $first = $dayRows[0];
            $last  = end($dayRows);
            // Build a synthetic row merging first origin with final destination
            $synthetic          = $first;
            $synthetic[COL_TO]  = $last[COL_TO];
            // If the whole day's sequence is entirely skip-flagged, mark synthetic skip too
            $allSkip = array_reduce($dayRows, fn($c, $r) => $c && (trim($r[COL_SKIP] ?? '') === 'Y'), true);
            $synthetic[COL_SKIP] = $allSkip ? 'Y' : '';
            $collapsed[] = $synthetic;
        }
    }
 
    return $collapsed;
}
 
/**
 * Aggregate an array of stays (country, from-date, to-date) into per-country totals.
 * Stays in the same country on different date ranges are summed.
 *
 * Total days: inclusive both ends.
 * Working days: same span, Saturdays and Sundays removed.
 */
function aggregateStaysC(array $stays, string $person): array {
    $totals = [];   // [ 'HK' => ['total'=>n, 'working'=>n], ... ]
 
    foreach ($stays as $stay) {
        $country = $stay['country'];
        $from    = $stay['from'];
        $to      = $stay['to'];
 
        $totalDays   = 0;
        $workingDays = 0;
 
        $cursor = clone $from;
        while ($cursor <= $to) {
            $totalDays++;
            $dow = (int)$cursor->format('N');  // 1=Mon … 7=Sun
            if ($dow < 6) $workingDays++;      // 6=Sat, 7=Sun
            $cursor->modify('+1 day');
        }
 
        if (!isset($totals[$country])) {
            $totals[$country] = ['total' => 0, 'working' => 0];
        }
        $totals[$country]['total']   += $totalDays;
        $totals[$country]['working'] += $workingDays;
    }
 
    // Build output with warning flag
    $output = ['person' => $person, 'jurisdictions' => []];
    foreach ($totals as $country => $counts) {
        $output['jurisdictions'][$country] = [
            'total'   => $counts['total'],
            'working' => $counts['working'],
            'warning' => $counts['total'] > C_WARNING_DAYS,
        ];
    }
 
    return $output;
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — PERSON K PROCESSING
// ---------------------------------------------------------------------------
 
/**
 * Process Person K: count HKG days only, for HK social security reporting.
 *
 * Rule:
 *   - Departure day from HKG is NOT counted
 *   - Return day to HKG IS counted
 *   - Days spent continuously in HKG (no travel) accumulate normally
 *
 * We walk K's collapsed flights. Whenever K is outside HKG, we note the
 * departure date and return date. HKG credit = days in HKG spans, with
 * departure day excluded and return day included.
 */
function processPersonK(string $person, array $allFlights, array $airports): array {
    $flights = filterAndCollapse($person, $allFlights);
 
    $hkgISO = resolveIATA('HKG', $airports, '260101', $person);
 
    $hkgDays   = 0;
    $currentLocation = null;
    $arrivedOn       = null;
    $firstFlight     = true;
    $jurisdictionsVisited = [];
 
    foreach ($flights as $f) {
        $date    = parseDate($f[COL_DATE]);
        $fromISO = resolveIATA($f[COL_FROM], $airports, $f[COL_DATE], $person);
        $toISO   = resolveIATA($f[COL_TO],   $airports, $f[COL_DATE], $person);
 
        $jurisdictionsVisited[$toISO] = true;
 
        if ($firstFlight) {
            $yearStart       = new DateTime($date->format('Y') . '-01-01');
            $arrivedOn       = $yearStart;
            $currentLocation = $fromISO;
            $firstFlight     = false;
        }
 
        if ($currentLocation !== $fromISO) {
            halt(
                "Continuity error for $person on {$f[COL_DATE]}: " .
                "expected in $currentLocation but departing from $fromISO ({$f[COL_FROM]}). " .
                "Please review iata_2026out.csv."
            );
        }
 
        // If currently in HKG and departing: count days in HKG EXCLUDING today (departure day)
        if ($currentLocation === $hkgISO) {
            $hkgDays += daysBetweenExcludingEnd($arrivedOn, $date);
        }
 
        $currentLocation = $toISO;
        $arrivedOn       = clone $date;   // arrival at new destination = today
    }
 
    // Year-end: if still in HKG, count remaining days through Dec 31 (no departure, so include all)
    if ($currentLocation === $hkgISO) {
        $yearEnd  = new DateTime($arrivedOn->format('Y') . '-12-31');
        $hkgDays += daysBetweenExcludingEnd($arrivedOn, (clone $yearEnd)->modify('+1 day'));
        // +1 day so the end-exclusive function includes Dec 31
    }
 
    return [
        'person'               => $person,
        'hkg_days'             => $hkgDays,
        'jurisdictions_visited'=> array_keys($jurisdictionsVisited),
    ];
}
 
/**
 * Count calendar days from $start (inclusive) up to but NOT including $end.
 * Used for K's HKG counting: arrival-day-inclusive, departure-day-exclusive.
 */
function daysBetweenExcludingEnd(DateTime $start, DateTime $end): int {
    $diff = $start->diff($end);
    return max(0, $diff->days);
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — UTILITIES
// ---------------------------------------------------------------------------
 
/**
 * Parse a YYMMDD date string into a DateTime object.
 * Assumes 21st century (20xx).
 */
function parseDate(string $yymmdd): DateTime {
    $y = '20' . substr($yymmdd, 0, 2);
    $m = substr($yymmdd, 2, 2);
    $d = substr($yymmdd, 4, 2);
    return new DateTime("$y-$m-$d");
}
 
/**
 * Look up an IATA code in the airports array.
 * Halts with a clear message if the code cannot be resolved —
 * the script does not guess.
 */
function resolveIATA(string $iata, array $airports, string $dateCtx, string $person): string {
    if (!isset($airports[$iata])) {
        halt(
            "Unknown IATA code '$iata' for person $person around date $dateCtx. " .
            "Ensure airports.csv includes this airport as large_airport or medium_airport."
        );
    }
    return $airports[$iata];
}
 
/**
 * Halt execution by throwing an exception with a clear message.
 * The HTML wrapper will render this prominently.
 */
function halt(string $message): never {
    throw new RuntimeException("⛔ DATA ERROR: $message");
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — DEEPSEEK API
// ---------------------------------------------------------------------------
 
/**
 * Send a query to DeepSeek asking about entry requirement changes
 * for the list of jurisdictions K visited this year.
 *
 * Returns [ responseText|null, errorMessage|null ]
 */
function queryDeepSeek(array $isoCodes): array {
    // Map ISO country codes to readable names for the query
    $names = array_map('isoToName', $isoCodes);
    $list  = implode(', ', array_filter($names));
 
    $query = "I am a Hong Kong resident (not a passport holder) travelling on an Australian passport. "
           . "Are there any recent significant changes to entry requirements for: $list "
           . "that apply to me in the last 3 months?";
 
    $payload = json_encode([
        'model'    => 'deepseek-chat',
        'messages' => [
            ['role' => 'user', 'content' => $query]
        ],
        'max_tokens' => 1024,
    ]);
 
    $ch = curl_init(DEEPSEEK_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEEPSEEK_KEY,
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
 
    $raw   = curl_exec($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
 
    if ($errno) return [null, "cURL error $errno — check network connectivity."];
 
    $data = json_decode($raw, true);
    if (isset($data['error'])) {
        return [null, "DeepSeek API error: " . ($data['error']['message'] ?? $raw)];
    }
 
    $text = $data['choices'][0]['message']['content'] ?? null;
    if ($text === null) return [null, "Unexpected response format from DeepSeek: $raw"];
 
    return [$text, null];
}
 
/**
 * Very basic ISO 3166-1 alpha-2 to country name mapping for common codes.
 * Falls back to the raw code if not found — good enough for the query string.
 */
function isoToName(string $iso): string {
    $map = [
        'AU'=>'Australia','CN'=>'China','FR'=>'France','DE'=>'Germany',
        'HK'=>'Hong Kong','IN'=>'India','ID'=>'Indonesia','JP'=>'Japan',
        'MY'=>'Malaysia','NZ'=>'New Zealand','PH'=>'Philippines',
        'SG'=>'Singapore','KR'=>'South Korea','TW'=>'Taiwan',
        'TH'=>'Thailand','GB'=>'United Kingdom','US'=>'United States',
        'VN'=>'Vietnam','AE'=>'UAE','CA'=>'Canada','IT'=>'Italy',
        'ES'=>'Spain','PT'=>'Portugal','NL'=>'Netherlands','CH'=>'Switzerland',
        'AT'=>'Austria','BE'=>'Belgium','SE'=>'Sweden','DK'=>'Denmark',
        'NO'=>'Norway','FI'=>'Finland','PL'=>'Poland','CZ'=>'Czech Republic',
        'HU'=>'Hungary','RO'=>'Romania','GR'=>'Greece','TR'=>'Turkey',
        'IL'=>'Israel','ZA'=>'South Africa','MX'=>'Mexico','BR'=>'Brazil',
        'AR'=>'Argentina','EG'=>'Egypt','MA'=>'Morocco','KE'=>'Kenya',
        'NG'=>'Nigeria','SA'=>'Saudi Arabia','QA'=>'Qatar','KW'=>'Kuwait',
        'BH'=>'Bahrain','OM'=>'Oman','JO'=>'Jordan','LB'=>'Lebanon',
        'PK'=>'Pakistan','BD'=>'Bangladesh','LK'=>'Sri Lanka','NP'=>'Nepal',
        'MM'=>'Myanmar','KH'=>'Cambodia','LA'=>'Laos','MV'=>'Maldives',
    ];
    return $map[$iso] ?? $iso;
}<?php
/**
 * Personal Travel Log Processor
 * ================================
 * Processes boarding pass scan data from iata_2026out.csv to produce:
 *   - Person C: Jurisdiction day counts (total + working days) for tax/work-limit reporting
 *   - Person K: HKG-only day count for HK social security reporting
 *
 * Stage 2 (button-triggered): DeepSeek API query on entry requirements for K's visited jurisdictions.
 *
 * Expects files in the same directory:
 *   ./iata_2026out.csv   — travel log (no header, see column map below)
 *   ./airports.csv       — ourairports.com full airport list
 */
 
// ---------------------------------------------------------------------------
// CONFIG
// ---------------------------------------------------------------------------
 
define('TRAVEL_CSV',   __DIR__ . '/iata_2026out.csv');
define('AIRPORTS_CSV', __DIR__ . '/airports.csv');
define('DEEPSEEK_KEY', 'dummykey');
define('DEEPSEEK_URL', 'https://api.deepseek.com/v1/chat/completions');
 
// Column indices in iata_2026out.csv (0-based, no header row)
const COL_DATE     = 0;   // YYMMDD
const COL_PERSON   = 1;   // C or K
const COL_FROM     = 2;   // Origin IATA
const COL_TO       = 3;   // Destination IATA
const COL_AIRLINE  = 4;
const COL_FLIGHT   = 5;
const COL_JULIAN   = 6;   // Julian day of year — used as secondary sort key
const COL_SKIP     = 7;   // Y = exclude this row from day-count calculations
 
// Warning threshold: print notice if total days in a jurisdiction exceeds this
const C_WARNING_DAYS = 90;
 
// ---------------------------------------------------------------------------
// BOOTSTRAP — load data, run Stage 1, render HTML
// ---------------------------------------------------------------------------
 
$errors   = [];
$results  = [];
 
try {
    $airports = loadAirports(AIRPORTS_CSV);
    $flights  = loadFlights(TRAVEL_CSV);
 
    $results['C'] = processPerson('C', $flights, $airports);
    $results['K'] = processPersonK('K', $flights, $airports);
 
} catch (RuntimeException $e) {
    $errors[] = $e->getMessage();
}
 
// Stage 2: DeepSeek call is POST-triggered from the button
$deepseekResponse = null;
$deepseekError    = null;
 
if (isset($_POST['run_deepseek']) && empty($errors)) {
    try {
        [$deepseekResponse, $deepseekError] = queryDeepSeek($results['K']['jurisdictions_visited'] ?? []);
    } catch (RuntimeException $e) {
        $deepseekError = $e->getMessage();
    }
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — DATA LOADING
// ---------------------------------------------------------------------------
 
/**
 * Load airports.csv into a lookup array keyed by IATA code.
 * Only large_airport and medium_airport types are indexed to avoid
 * false matches with closed or helipad facilities sharing an IATA code.
 * Returns: [ 'HKG' => 'HK', 'LHR' => 'GB', ... ]
 */
function loadAirports(string $path): array {
    if (!file_exists($path)) halt("airports.csv not found at: $path");
 
    $handle = fopen($path, 'r');
    $header = fgetcsv($handle);   // ourairports.com has a header row
 
    // Map header names to column indices — file format may vary slightly
    $idx = array_flip($header);
    $iataCol    = $idx['iata_code']   ?? null;
    $typeCol    = $idx['type']        ?? null;
    $countryCol = $idx['iso_country'] ?? null;
 
    if ($iataCol === null || $typeCol === null || $countryCol === null) {
        halt("airports.csv is missing expected columns: iata_code, type, iso_country");
    }
 
    $lookup = [];
    while (($row = fgetcsv($handle)) !== false) {
        $type = trim($row[$typeCol] ?? '');
        if (!in_array($type, ['large_airport', 'medium_airport'], true)) continue;
 
        $iata    = trim($row[$iataCol]    ?? '');
        $country = trim($row[$countryCol] ?? '');
        if ($iata === '' || $country === '') continue;
 
        $lookup[$iata] = $country;
    }
    fclose($handle);
 
    return $lookup;
}
 
/**
 * Load and sort iata_2026out.csv.
 * Rows are returned sorted ascending by Date (col 0) then Julian day (col 6).
 * Raw file is in reverse chronological order, so sorting is always required.
 */
function loadFlights(string $path): array {
    if (!file_exists($path)) halt("iata_2026out.csv not found at: $path");
 
    $rows = [];
    $handle = fopen($path, 'r');
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 7) continue;  // skip malformed/blank lines
        $rows[] = $row;
    }
    fclose($handle);
 
    // Sort ascending: primary = date string (YYMMDD sorts correctly as string),
    // secondary = Julian day as integer
    usort($rows, function($a, $b) {
        $dateCmp = strcmp($a[COL_DATE], $b[COL_DATE]);
        if ($dateCmp !== 0) return $dateCmp;
        return (int)$a[COL_JULIAN] <=> (int)$b[COL_JULIAN];
    });
 
    return $rows;
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — PERSON C PROCESSING
// ---------------------------------------------------------------------------
 
/**
 * Process all flights for a given person and return jurisdiction day summaries.
 *
 * Strategy:
 *   1. Filter rows for this person, collapse same-day multi-leg sequences into
 *      a single logical movement (first origin → final destination only).
 *   2. Walk the collapsed movements in order, tracking current location and
 *      the date the person arrived there.
 *   3. When a departure from a location is seen, close out that stay and
 *      calculate total/working days for that jurisdiction span.
 *   4. Handle year-start and year-end exceptions.
 */
function processPerson(string $person, array $allFlights, array $airports): array {
 
    $flights = filterAndCollapse($person, $allFlights);
 
    // We build a list of stays: [ ['country'=>'HK', 'from'=>DateTime, 'to'=>DateTime], ... ]
    // from/to are the inclusive first and last calendar dates in that jurisdiction.
    $stays = [];
 
    // currentLocation: where the person is right now as we walk forward
    // arrivedOn: the date they arrived at currentLocation
    $currentLocation = null;
    $arrivedOn       = null;
    $firstFlight     = true;
 
    foreach ($flights as $f) {
        $date    = parseDate($f[COL_DATE]);
        $from    = $f[COL_FROM];
        $to      = $f[COL_TO];
        $fromISO = resolveIATA($from, $airports, $f[COL_DATE], $person);
        $toISO   = resolveIATA($to,   $airports, $f[COL_DATE], $person);
 
        if ($firstFlight) {
            // Year-start exception: accept without continuity check.
            // Person is treated as having been at $from since Jan 1.
            $yearStart = new DateTime("20{$f[COL_DATE][0]}{$f[COL_DATE][1]}-01-01");
            $arrivedOn       = $yearStart;
            $currentLocation = $fromISO;
            $firstFlight     = false;
        }
 
        // Continuity check: their current known location must match this flight's origin
        if ($currentLocation !== $fromISO) {
            halt(
                "Continuity error for $person on {$f[COL_DATE]}: " .
                "expected to be in $currentLocation but flight departs from $fromISO ($from). " .
                "Please review iata_2026out.csv."
            );
        }
 
        // Close out the stay in $currentLocation: they were there from $arrivedOn to $date (departure day)
        $stays[] = [
            'country' => $currentLocation,
            'from'    => clone $arrivedOn,
            'to'      => clone $date,
        ];
 
        // Move to destination
        $currentLocation = $toISO;
        $arrivedOn       = clone $date;
    }
 
    // Year-end exception: person remains at $currentLocation through Dec 31
    if ($currentLocation !== null) {
        $lastDate  = $arrivedOn;
        $yearStr   = '20' . substr(array_values(array_filter(
            $allFlights,
            fn($r) => trim($r[COL_PERSON]) === $person
        ))[0][COL_DATE] ?? '260101', 0, 2);
        // Derive year from the last flight date we processed
        $yearEnd = new DateTime($lastDate->format('Y') . '-12-31');
        $stays[] = [
            'country' => $currentLocation,
            'from'    => clone $arrivedOn,
            'to'      => $yearEnd,
        ];
    }
 
    // Aggregate stays by country
    return aggregateStaysC($stays, $person);
}
 
/**
 * Filter flights for a person, remove skip-flagged rows from day counts,
 * then collapse same-day multi-leg journeys:
 *   HKG→LHR→CDG on the same day becomes a single record HKG→CDG.
 *
 * Skip-flagged rows are still read (kept in the pre-collapse sequence for
 * continuity) but their intermediate airports are never surfaced as
 * independent jurisdiction entries.
 *
 * Returns an array of simplified flight rows with the same column structure.
 */
function filterAndCollapse(string $person, array $allFlights): array {
    // Keep only this person's rows
    $mine = array_values(array_filter(
        $allFlights,
        fn($r) => trim($r[COL_PERSON]) === $person
    ));
 
    if (empty($mine)) halt("No flight records found for person '$person'.");
 
    // Group by date
    $byDate = [];
    foreach ($mine as $row) {
        $byDate[$row[COL_DATE]][] = $row;
    }
 
    $collapsed = [];
    foreach ($byDate as $date => $dayRows) {
        if (count($dayRows) === 1) {
            // Single flight day — use as-is (skip flag still respected below)
            $collapsed[] = $dayRows[0];
        } else {
            // Multi-leg day: first origin → final destination
            // Rows are already sorted by Julian day within the date
            $first = $dayRows[0];
            $last  = end($dayRows);
            // Build a synthetic row merging first origin with final destination
            $synthetic          = $first;
            $synthetic[COL_TO]  = $last[COL_TO];
            // If the whole day's sequence is entirely skip-flagged, mark synthetic skip too
            $allSkip = array_reduce($dayRows, fn($c, $r) => $c && (trim($r[COL_SKIP] ?? '') === 'Y'), true);
            $synthetic[COL_SKIP] = $allSkip ? 'Y' : '';
            $collapsed[] = $synthetic;
        }
    }
 
    return $collapsed;
}
 
/**
 * Aggregate an array of stays (country, from-date, to-date) into per-country totals.
 * Stays in the same country on different date ranges are summed.
 *
 * Total days: inclusive both ends.
 * Working days: same span, Saturdays and Sundays removed.
 */
function aggregateStaysC(array $stays, string $person): array {
    $totals = [];   // [ 'HK' => ['total'=>n, 'working'=>n], ... ]
 
    foreach ($stays as $stay) {
        $country = $stay['country'];
        $from    = $stay['from'];
        $to      = $stay['to'];
 
        $totalDays   = 0;
        $workingDays = 0;
 
        $cursor = clone $from;
        while ($cursor <= $to) {
            $totalDays++;
            $dow = (int)$cursor->format('N');  // 1=Mon … 7=Sun
            if ($dow < 6) $workingDays++;      // 6=Sat, 7=Sun
            $cursor->modify('+1 day');
        }
 
        if (!isset($totals[$country])) {
            $totals[$country] = ['total' => 0, 'working' => 0];
        }
        $totals[$country]['total']   += $totalDays;
        $totals[$country]['working'] += $workingDays;
    }
 
    // Build output with warning flag
    $output = ['person' => $person, 'jurisdictions' => []];
    foreach ($totals as $country => $counts) {
        $output['jurisdictions'][$country] = [
            'total'   => $counts['total'],
            'working' => $counts['working'],
            'warning' => $counts['total'] > C_WARNING_DAYS,
        ];
    }
 
    return $output;
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — PERSON K PROCESSING
// ---------------------------------------------------------------------------
 
/**
 * Process Person K: count HKG days only, for HK social security reporting.
 *
 * Rule:
 *   - Departure day from HKG is NOT counted
 *   - Return day to HKG IS counted
 *   - Days spent continuously in HKG (no travel) accumulate normally
 *
 * We walk K's collapsed flights. Whenever K is outside HKG, we note the
 * departure date and return date. HKG credit = days in HKG spans, with
 * departure day excluded and return day included.
 */
function processPersonK(string $person, array $allFlights, array $airports): array {
    $flights = filterAndCollapse($person, $allFlights);
 
    $hkgISO = resolveIATA('HKG', $airports, '260101', $person);
 
    $hkgDays   = 0;
    $currentLocation = null;
    $arrivedOn       = null;
    $firstFlight     = true;
    $jurisdictionsVisited = [];
 
    foreach ($flights as $f) {
        $date    = parseDate($f[COL_DATE]);
        $fromISO = resolveIATA($f[COL_FROM], $airports, $f[COL_DATE], $person);
        $toISO   = resolveIATA($f[COL_TO],   $airports, $f[COL_DATE], $person);
 
        $jurisdictionsVisited[$toISO] = true;
 
        if ($firstFlight) {
            $yearStart       = new DateTime($date->format('Y') . '-01-01');
            $arrivedOn       = $yearStart;
            $currentLocation = $fromISO;
            $firstFlight     = false;
        }
 
        if ($currentLocation !== $fromISO) {
            halt(
                "Continuity error for $person on {$f[COL_DATE]}: " .
                "expected in $currentLocation but departing from $fromISO ({$f[COL_FROM]}). " .
                "Please review iata_2026out.csv."
            );
        }
 
        // If currently in HKG and departing: count days in HKG EXCLUDING today (departure day)
        if ($currentLocation === $hkgISO) {
            $hkgDays += daysBetweenExcludingEnd($arrivedOn, $date);
        }
 
        $currentLocation = $toISO;
        $arrivedOn       = clone $date;   // arrival at new destination = today
    }
 
    // Year-end: if still in HKG, count remaining days through Dec 31 (no departure, so include all)
    if ($currentLocation === $hkgISO) {
        $yearEnd  = new DateTime($arrivedOn->format('Y') . '-12-31');
        $hkgDays += daysBetweenExcludingEnd($arrivedOn, (clone $yearEnd)->modify('+1 day'));
        // +1 day so the end-exclusive function includes Dec 31
    }
 
    return [
        'person'               => $person,
        'hkg_days'             => $hkgDays,
        'jurisdictions_visited'=> array_keys($jurisdictionsVisited),
    ];
}
 
/**
 * Count calendar days from $start (inclusive) up to but NOT including $end.
 * Used for K's HKG counting: arrival-day-inclusive, departure-day-exclusive.
 */
function daysBetweenExcludingEnd(DateTime $start, DateTime $end): int {
    $diff = $start->diff($end);
    return max(0, $diff->days);
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — UTILITIES
// ---------------------------------------------------------------------------
 
/**
 * Parse a YYMMDD date string into a DateTime object.
 * Assumes 21st century (20xx).
 */
function parseDate(string $yymmdd): DateTime {
    $y = '20' . substr($yymmdd, 0, 2);
    $m = substr($yymmdd, 2, 2);
    $d = substr($yymmdd, 4, 2);
    return new DateTime("$y-$m-$d");
}
 
/**
 * Look up an IATA code in the airports array.
 * Halts with a clear message if the code cannot be resolved —
 * the script does not guess.
 */
function resolveIATA(string $iata, array $airports, string $dateCtx, string $person): string {
    if (!isset($airports[$iata])) {
        halt(
            "Unknown IATA code '$iata' for person $person around date $dateCtx. " .
            "Ensure airports.csv includes this airport as large_airport or medium_airport."
        );
    }
    return $airports[$iata];
}
 
/**
 * Halt execution by throwing an exception with a clear message.
 * The HTML wrapper will render this prominently.
 */
function halt(string $message): never {
    throw new RuntimeException("⛔ DATA ERROR: $message");
}
 
// ---------------------------------------------------------------------------
// FUNCTIONS — DEEPSEEK API
// ---------------------------------------------------------------------------
 
/**
 * Send a query to DeepSeek asking about entry requirement changes
 * for the list of jurisdictions K visited this year.
 *
 * Returns [ responseText|null, errorMessage|null ]
 */
function queryDeepSeek(array $isoCodes): array {
    // Map ISO country codes to readable names for the query
    $names = array_map('isoToName', $isoCodes);
    $list  = implode(', ', array_filter($names));
 
    $query = "I am a Hong Kong resident (not a passport holder) travelling on an Australian passport. "
           . "Are there any recent significant changes to entry requirements for: $list "
           . "that apply to me in the last 3 months?";
 
    $payload = json_encode([
        'model'    => 'deepseek-chat',
        'messages' => [
            ['role' => 'user', 'content' => $query]
        ],
        'max_tokens' => 1024,
    ]);
 
    $ch = curl_init(DEEPSEEK_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEEPSEEK_KEY,
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
 
    $raw   = curl_exec($ch);
    $errno = curl_errno($ch);
    curl_close($ch);
 
    if ($errno) return [null, "cURL error $errno — check network connectivity."];
 
    $data = json_decode($raw, true);
    if (isset($data['error'])) {
        return [null, "DeepSeek API error: " . ($data['error']['message'] ?? $raw)];
    }
 
    $text = $data['choices'][0]['message']['content'] ?? null;
    if ($text === null) return [null, "Unexpected response format from DeepSeek: $raw"];
 
    return [$text, null];
}
 
/**
 * Very basic ISO 3166-1 alpha-2 to country name mapping for common codes.
 * Falls back to the raw code if not found — good enough for the query string.
 */
function isoToName(string $iso): string {
    $map = [
        'AU'=>'Australia','CN'=>'China','FR'=>'France','DE'=>'Germany',
        'HK'=>'Hong Kong','IN'=>'India','ID'=>'Indonesia','JP'=>'Japan',
        'MY'=>'Malaysia','NZ'=>'New Zealand','PH'=>'Philippines',
        'SG'=>'Singapore','KR'=>'South Korea','TW'=>'Taiwan',
        'TH'=>'Thailand','GB'=>'United Kingdom','US'=>'United States',
        'VN'=>'Vietnam','AE'=>'UAE','CA'=>'Canada','IT'=>'Italy',
        'ES'=>'Spain','PT'=>'Portugal','NL'=>'Netherlands','CH'=>'Switzerland',
        'AT'=>'Austria','BE'=>'Belgium','SE'=>'Sweden','DK'=>'Denmark',
        'NO'=>'Norway','FI'=>'Finland','PL'=>'Poland','CZ'=>'Czech Republic',
        'HU'=>'Hungary','RO'=>'Romania','GR'=>'Greece','TR'=>'Turkey',
        'IL'=>'Israel','ZA'=>'South Africa','MX'=>'Mexico','BR'=>'Brazil',
        'AR'=>'Argentina','EG'=>'Egypt','MA'=>'Morocco','KE'=>'Kenya',
        'NG'=>'Nigeria','SA'=>'Saudi Arabia','QA'=>'Qatar','KW'=>'Kuwait',
        'BH'=>'Bahrain','OM'=>'Oman','JO'=>'Jordan','LB'=>'Lebanon',
        'PK'=>'Pakistan','BD'=>'Bangladesh','LK'=>'Sri Lanka','NP'=>'Nepal',
        'MM'=>'Myanmar','KH'=>'Cambodia','LA'=>'Laos','MV'=>'Maldives',
    ];
    return $map[$iso] ?? $iso;
}
 
// ---------------------------------------------------------------------------
// HTML OUTPUT
// ---------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Log Report <?= date('Y') ?></title>
<style>
  /* ── Design: clean monospaced terminal aesthetic — suits data output and
        is easy to read at a glance. Dark background reduces eye strain for
        long sessions of reviewing figures. ── */
 
  @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;600&display=swap');
 
  :root {
    --bg:       #0f1117;
    --surface:  #1a1d27;
    --border:   #2a2d3d;
    --accent:   #4fc3f7;
    --warn:     #ffb74d;
    --error:    #ef5350;
    --ok:       #66bb6a;
    --text:     #e0e4f0;
    --muted:    #6b7494;
    --mono:     'IBM Plex Mono', monospace;
    --sans:     'IBM Plex Sans', sans-serif;
  }
 
  * { box-sizing: border-box; margin: 0; padding: 0; }
 
  body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--sans);
    font-size: 15px;
    line-height: 1.6;
    padding: 2rem;
    max-width: 960px;
    margin: 0 auto;
  }
 
  h1 {
    font-family: var(--mono);
    font-size: 1.4rem;
    color: var(--accent);
    letter-spacing: 0.04em;
    margin-bottom: 0.25rem;
  }
 
  .subtitle {
    font-family: var(--mono);
    font-size: 0.8rem;
    color: var(--muted);
    margin-bottom: 2.5rem;
  }
 
  h2 {
    font-family: var(--mono);
    font-size: 1rem;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 1rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
  }
 
  section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
 
  table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--mono);
    font-size: 0.88rem;
  }
 
  th {
    text-align: left;
    color: var(--muted);
    font-weight: 600;
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid var(--border);
    letter-spacing: 0.06em;
    font-size: 0.78rem;
    text-transform: uppercase;
  }
 
  td {
    padding: 0.45rem 0.75rem;
    border-bottom: 1px solid #1f2235;
  }
 
  tr:last-child td { border-bottom: none; }
 
  .warn-row td { color: var(--warn); }
 
  .tag {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 3px;
    font-size: 0.75rem;
    font-family: var(--mono);
    font-weight: 600;
  }
 
  .tag-warn { background: rgba(255,183,77,0.15); color: var(--warn); }
  .tag-ok   { background: rgba(102,187,106,0.15); color: var(--ok); }
 
  .big-number {
    font-family: var(--mono);
    font-size: 2.5rem;
    font-weight: 600;
    color: var(--accent);
    line-height: 1;
    margin-bottom: 0.25rem;
  }
 
  .big-label {
    font-family: var(--mono);
    font-size: 0.78rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
 
  .error-box {
    background: rgba(239,83,80,0.1);
    border: 1px solid var(--error);
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    font-family: var(--mono);
    font-size: 0.9rem;
    color: var(--error);
    margin-bottom: 1.5rem;
    white-space: pre-wrap;
  }
 
  button {
    background: transparent;
    border: 1px solid var(--accent);
    color: var(--accent);
    font-family: var(--mono);
    font-size: 0.88rem;
    padding: 0.6rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    letter-spacing: 0.06em;
    transition: background 0.2s, color 0.2s;
  }
 
  button:hover {
    background: var(--accent);
    color: var(--bg);
  }
 
  .deepseek-response {
    font-family: var(--sans);
    font-size: 0.9rem;
    line-height: 1.7;
    color: var(--text);
    white-space: pre-wrap;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
  }
 
  .deepseek-error {
    color: var(--error);
    font-family: var(--mono);
    font-size: 0.88rem;
    margin-top: 1rem;
  }
</style>
</head>
<body>
 
<h1>✈ Travel Log Report</h1>
<div class="subtitle">Generated <?= date('Y-m-d H:i') ?> · Source: iata_2026out.csv</div>
 
<?php if (!empty($errors)): ?>
  <?php foreach ($errors as $e): ?>
    <div class="error-box"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>
<?php endif; ?>
 
<?php if (!empty($results) && empty($errors)): ?>
 
<!-- ── PERSON C ── -->
<section>
  <h2>Person C — Jurisdiction Days (Tax / Work Limits)</h2>
  <?php
    $juris = $results['C']['jurisdictions'] ?? [];
    // Sort descending by total days for readability
    uasort($juris, fn($a, $b) => $b['total'] <=> $a['total']);
  ?>
  <table>
    <thead>
      <tr>
        <th>Jurisdiction</th>
        <th style="text-align:right">Total Days</th>
        <th style="text-align:right">Working Days</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($juris as $iso => $data): ?>
      <tr class="<?= $data['warning'] ? 'warn-row' : '' ?>">
        <td><?= htmlspecialchars(isoToName($iso)) ?> <span style="color:var(--muted)">(<?= htmlspecialchars($iso) ?>)</span></td>
        <td style="text-align:right"><?= $data['total'] ?></td>
        <td style="text-align:right"><?= $data['working'] ?></td>
        <td>
          <?php if ($data['warning']): ?>
            <span class="tag tag-warn">⚠ EXCEEDS 90</span>
          <?php else: ?>
            <span class="tag tag-ok">OK</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
 
<!-- ── PERSON K ── -->
<section>
  <h2>Person K — HKG Days (Social Security)</h2>
  <div class="big-number"><?= $results['K']['hkg_days'] ?></div>
  <div class="big-label">Days credited in Hong Kong</div>
  <p style="font-size:0.82rem;color:var(--muted);margin-top:0.75rem;font-family:var(--mono)">
    Rule: departure day excluded · return day included
  </p>
</section>
 
<!-- ── STAGE 2 — DEEPSEEK ── -->
<section>
  <h2>Entry Requirements Check — Person K</h2>
  <p style="font-size:0.88rem;color:var(--muted);margin-bottom:1.25rem">
    Queries DeepSeek for recent entry requirement changes relevant to an
    Australian passport holder / HK resident for K's visited jurisdictions.
  </p>
 
  <form method="post">
    <button type="submit" name="run_deepseek" value="1">
      ▶ Check Entry Requirements
    </button>
  </form>
 
  <?php if ($deepseekResponse !== null): ?>
    <div class="deepseek-response"><?= htmlspecialchars($deepseekResponse) ?></div>
  <?php endif; ?>
 
  <?php if ($deepseekError !== null): ?>
    <div class="deepseek-error">⛔ <?= htmlspecialchars($deepseekError) ?></div>
  <?php endif; ?>
</section>
 
<?php endif; ?>
 
</body>
</html>

 
// ---------------------------------------------------------------------------
// HTML OUTPUT
// ---------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Travel Log Report <?= date('Y') ?></title>
<style>
  /* ── Design: clean monospaced terminal aesthetic — suits data output and
        is easy to read at a glance. Dark background reduces eye strain for
        long sessions of reviewing figures. ── */
 
  @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;600&display=swap');
 
  :root {
    --bg:       #0f1117;
    --surface:  #1a1d27;
    --border:   #2a2d3d;
    --accent:   #4fc3f7;
    --warn:     #ffb74d;
    --error:    #ef5350;
    --ok:       #66bb6a;
    --text:     #e0e4f0;
    --muted:    #6b7494;
    --mono:     'IBM Plex Mono', monospace;
    --sans:     'IBM Plex Sans', sans-serif;
  }
 
  * { box-sizing: border-box; margin: 0; padding: 0; }
 
  body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--sans);
    font-size: 15px;
    line-height: 1.6;
    padding: 2rem;
    max-width: 960px;
    margin: 0 auto;
  }
 
  h1 {
    font-family: var(--mono);
    font-size: 1.4rem;
    color: var(--accent);
    letter-spacing: 0.04em;
    margin-bottom: 0.25rem;
  }
 
  .subtitle {
    font-family: var(--mono);
    font-size: 0.8rem;
    color: var(--muted);
    margin-bottom: 2.5rem;
  }
 
  h2 {
    font-family: var(--mono);
    font-size: 1rem;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 1rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
  }
 
  section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
 
  table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--mono);
    font-size: 0.88rem;
  }
 
  th {
    text-align: left;
    color: var(--muted);
    font-weight: 600;
    padding: 0.4rem 0.75rem;
    border-bottom: 1px solid var(--border);
    letter-spacing: 0.06em;
    font-size: 0.78rem;
    text-transform: uppercase;
  }
 
  td {
    padding: 0.45rem 0.75rem;
    border-bottom: 1px solid #1f2235;
  }
 
  tr:last-child td { border-bottom: none; }
 
  .warn-row td { color: var(--warn); }
 
  .tag {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 3px;
    font-size: 0.75rem;
    font-family: var(--mono);
    font-weight: 600;
  }
 
  .tag-warn { background: rgba(255,183,77,0.15); color: var(--warn); }
  .tag-ok   { background: rgba(102,187,106,0.15); color: var(--ok); }
 
  .big-number {
    font-family: var(--mono);
    font-size: 2.5rem;
    font-weight: 600;
    color: var(--accent);
    line-height: 1;
    margin-bottom: 0.25rem;
  }
 
  .big-label {
    font-family: var(--mono);
    font-size: 0.78rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
 
  .error-box {
    background: rgba(239,83,80,0.1);
    border: 1px solid var(--error);
    border-radius: 6px;
    padding: 1.25rem 1.5rem;
    font-family: var(--mono);
    font-size: 0.9rem;
    color: var(--error);
    margin-bottom: 1.5rem;
    white-space: pre-wrap;
  }
 
  button {
    background: transparent;
    border: 1px solid var(--accent);
    color: var(--accent);
    font-family: var(--mono);
    font-size: 0.88rem;
    padding: 0.6rem 1.5rem;
    border-radius: 4px;
    cursor: pointer;
    letter-spacing: 0.06em;
    transition: background 0.2s, color 0.2s;
  }
 
  button:hover {
    background: var(--accent);
    color: var(--bg);
  }
 
  .deepseek-response {
    font-family: var(--sans);
    font-size: 0.9rem;
    line-height: 1.7;
    color: var(--text);
    white-space: pre-wrap;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
  }
 
  .deepseek-error {
    color: var(--error);
    font-family: var(--mono);
    font-size: 0.88rem;
    margin-top: 1rem;
  }
</style>
</head>
<body>
 
<h1>✈ Travel Log Report</h1>
<div class="subtitle">Generated <?= date('Y-m-d H:i') ?> · Source: iata_2026out.csv</div>
 
<?php if (!empty($errors)): ?>
  <?php foreach ($errors as $e): ?>
    <div class="error-box"><?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>
<?php endif; ?>
 
<?php if (!empty($results) && empty($errors)): ?>
 
<!-- ── PERSON C ── -->
<section>
  <h2>Person C — Jurisdiction Days (Tax / Work Limits)</h2>
  <?php
    $juris = $results['C']['jurisdictions'] ?? [];
    // Sort descending by total days for readability
    uasort($juris, fn($a, $b) => $b['total'] <=> $a['total']);
  ?>
  <table>
    <thead>
      <tr>
        <th>Jurisdiction</th>
        <th style="text-align:right">Total Days</th>
        <th style="text-align:right">Working Days</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($juris as $iso => $data): ?>
      <tr class="<?= $data['warning'] ? 'warn-row' : '' ?>">
        <td><?= htmlspecialchars(isoToName($iso)) ?> <span style="color:var(--muted)">(<?= htmlspecialchars($iso) ?>)</span></td>
        <td style="text-align:right"><?= $data['total'] ?></td>
        <td style="text-align:right"><?= $data['working'] ?></td>
        <td>
          <?php if ($data['warning']): ?>
            <span class="tag tag-warn">⚠ EXCEEDS 90</span>
          <?php else: ?>
            <span class="tag tag-ok">OK</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>
 
<!-- ── PERSON K ── -->
<section>
  <h2>Person K — HKG Days (Social Security)</h2>
  <div class="big-number"><?= $results['K']['hkg_days'] ?></div>
  <div class="big-label">Days credited in Hong Kong</div>
  <p style="font-size:0.82rem;color:var(--muted);margin-top:0.75rem;font-family:var(--mono)">
    Rule: departure day excluded · return day included
  </p>
</section>
 
<!-- ── STAGE 2 — DEEPSEEK ── -->
<section>
  <h2>Entry Requirements Check — Person K</h2>
  <p style="font-size:0.88rem;color:var(--muted);margin-bottom:1.25rem">
    Queries DeepSeek for recent entry requirement changes relevant to an
    Australian passport holder / HK resident for K's visited jurisdictions.
  </p>
 
  <form method="post">
    <button type="submit" name="run_deepseek" value="1">
      ▶ Check Entry Requirements
    </button>
  </form>
 
  <?php if ($deepseekResponse !== null): ?>
    <div class="deepseek-response"><?= htmlspecialchars($deepseekResponse) ?></div>
  <?php endif; ?>
 
  <?php if ($deepseekError !== null): ?>
    <div class="deepseek-error">⛔ <?= htmlspecialchars($deepseekError) ?></div>
  <?php endif; ?>
</section>
 
<?php endif; ?>
 
</body>
</html>
