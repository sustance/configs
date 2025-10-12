<?php
// simple_parallel_ping.php
function simpleParallelPing($hosts, $count = 2) {
    $results = [];
    $processes = [];
    
    foreach ($hosts as $host) {
        // Use ping command (adjust for your OS - this works on Linux/Unix)
        $command = "ping -c {$count} " . escapeshellarg($host) . " 2>&1";
        $pipes = [];
        
        $process = proc_open($command, [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout  
            2 => ['pipe', 'w']  // stderr
        ], $pipes);
        
        if (is_resource($process)) {
            $processes[$host] = [
                'process' => $process,
                'pipes' => $pipes,
                'start' => microtime(true)
            ];
            
            // Close stdin since we don't need to write to it
            fclose($pipes[0]);
        }
    }
    
    // Check processes
    while (!empty($processes)) {
        foreach ($processes as $host => $data) {
            $status = proc_get_status($data['process']);
            
            if (!$status['running']) {
                // Process finished
                $output = stream_get_contents($data['pipes'][1]);
                $error = stream_get_contents($data['pipes'][2]);
                
                $responseTime = round((microtime(true) - $data['start']) * 1000, 2);
                
                // Parse ping output to see if successful
                $success = (strpos($output, "{$count} received") !== false) || 
                          (strpos($output, "bytes from") !== false);
                
                $results[$host] = [
                    'success' => $success,
                    'output' => $output,
                    'error' => $error,
                    'response_time' => $responseTime,
                    'exitcode' => $status['exitcode']
                ];
                
                // Clean up
                fclose($data['pipes'][1]);
                fclose($data['pipes'][2]);
                proc_close($data['process']);
                unset($processes[$host]);
            }
        }
        
        if (!empty($processes)) {
            usleep(100000); // Wait 100ms before checking again
        }
    }
    
    return $results;
}

// Example usage
$servers = [
    'bsd.tilde.team',
    'ctrl-c.club',
    'dimension.sh',
    'envs.net',
    'core.envs.net',
    'freeshell.de',
    'tilde.guru',
    'thunix.net',
    'tilde.institute',
    'projectsegfau.lt',
    'p.projectsegfau.lt',
    'tilde.pink',
    'tilde.team',
    'vern.cc',
    'rawtext.club',
    'github.io',
    'sustance.github.io',
    'sufbo.tplinkdns.com',
    '127.0.0.1'
];

echo "\nStart parallel pings...\n";
$start = microtime(true);
$results = simpleParallelPing($servers, 2); // 2 ping packets per host
$end = microtime(true);

foreach ($results as $server => $result) {
    $status = $result['success'] ? '✓ ONLINE' : '✗ OFFLINE';
    $time = $result['response_time'];
    echo "{$status} ({$time}ms) {$server}\n";
    
    if (!$result['success']) {
        echo "  Error: Exit code {$result['exitcode']}\n";
    }
}

echo "\n\nPAST SAMPLE FROM SHA
		✓ ONLINE (1105.08ms) 127.0.0.1
		✓ ONLINE (1208.19ms) github.io
		✓ ONLINE (1207.43ms) sustance.github.io
		✓ ONLINE (1206.59ms) sufbo.tplinkdns.com
		✓ ONLINE (1318.58ms) envs.net
		✓ ONLINE (1315.74ms) tilde.institute
		✓ ONLINE (1420.48ms) bsd.tilde.team
		✓ ONLINE (1420.15ms) ctrl-c.club
		✓ ONLINE (1419.77ms) dimension.sh
		✓ ONLINE (1418.66ms) freeshell.de
		✓ ONLINE (1418.23ms) tilde.guru
		✓ ONLINE (1417.17ms) thunix.net
		✓ ONLINE (1413.86ms) tilde.pink
		✓ ONLINE (1413.15ms) tilde.team
		✓ ONLINE (1410.96ms) rawtext.club
		✗ OFFLINE (11259.68ms) core.envs.net
		  Error: Exit code 1
		✗ OFFLINE (11255.24ms) projectsegfau.lt
		  Error: Exit code 1
		✗ OFFLINE (11254.78ms) p.projectsegfau.lt
		  Error: Exit code 1
		✗ OFFLINE (11253.03ms) vern.cc
		  Error: Exit code 1
"



?>
