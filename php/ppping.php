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
    'tilde.pink',
    'tilde.team',
    'vern.cc',
    'rawtext.club',
    'sufbo.tplinkdns.com'
];

echo "___Start_parallel_pings___\n";
$start = microtime(true);
$results = simpleParallelPing($servers, 2); // 2 ping packets per host
$end = microtime(true);

foreach ($results as $server => $result) {
    $status = $result['success'] ? ' ✓' : ' ✗';
    $time = $result['response_time'];
    echo "{$status} ({$time}ms) {$server}<br>\n";
    
    if (!$result['success']) {
        echo "                Exit code: {$result['exitcode']}<br>\n";
    }
}
echo "<br> ✓ b,c,d,e,g,h,i,p,t,x,4,7  ? o,j  ✗ s-down f,policy<br>
 ssh -D 9999 -p 2007 aaa@sufbo.tplinkdns.com"
?>
