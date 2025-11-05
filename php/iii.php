<?php
// Fetch JSON data
$jsonUrl = 'https://raw.githubusercontent.com/sustance/configs/refs/heads/main/status_servers.json';
$jsonData = file_get_contents($jsonUrl);
$data = json_decode($jsonData, true);

// Sort servers by name
usort($data['servers'], function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<p class="fBSD"><a href=""></a> <a href=""></a> <a href="ctrl-c.club/~identity2"></a> 165.227.127.54 </p>


<?php
// Output each server
foreach ($data['servers'] as $server) {
    $osClass = $server['os']
    //echo "<p class='$osClass'>";
    
    // Basic server info
    //echo "<b><span class='fBSD'><u>
    echo "<p> text"
    echo "{$server['name']}";
    //echo "</u></span> 
    
    echo "{$server['country']} ";
    
    echo "<a href=\"{$server['host_url']}\">{$server['host_url']}</a> ";
    
    echo "<a href=\"{$server['url_own']}\">{$server['url_own']}</a> ";
    
    echo "<a href=\"{$server['url']}\">{$server['acc_name_s']}</a> ";
    
    echo "{$server['ip_address']} ";
    
    // Links from links_http array
    if (isset($server['links_http']) && is_array($server['links_http'])) {
        foreach ($server['links_http'] as $link) {
            $linkUrl = $server['url'] . '/' . $link . '.php';
            echo "<a href=\"$linkUrl\">$link</a> ";
        }
    }
    
    echo "</p>\n";
}
?>



<!--

<div class="bsd">
<H1>TEST DYNO</H1>
</div>

<div class="bsd">
<p>
US.
<a href="https://bsd.tilde.team/"> <b>b</b>sd.tilde.team </a> 
<a href="https://bsd.tilde.team/~identity2">/~i52</a> 
<a href="http://b.identity2.com">b.i.c</a>
<span class="sml">157.90.196.52</span> 
<span style="margin-left:9px;" class="sml"></span> 
</div>
<pre>
C
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

E
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

F
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

G
NO PHP

H
PHP Fatal error:  Uncaught Error: Call to undefined function curl_exec() in /home/id/eval.php:114
Stack trace:
#0 /home/id/eval.php(42): DynamicExecutionTester->testCurlDirect()
#1 /home/id/eval.php(34): DynamicExecutionTester->testMethod()
#2 /home/id/eval.php(278): DynamicExecutionTester->testAllMethods()
#3 {main}
  thrown in /home/id/eval.php on line 114

 I
allow_url_fopen          : DISABLED
allow_url_include        : DISABLED
curl_extension           : MISSING
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

O J
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback


P
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : MISSING
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

T
allow_url_fopen          : ENABLED
allow_url_include        : DISABLED
curl_extension           : AVAILABLE
write_permissions        : WRITABLE
ALL_DYNAMIC_METHODS_FAIL - Use cron fallback

</pre>
-->




<?php
$tail = file_get_contents('https://raw.githubusercontent.com/sustance/sustance.github.io/refs/heads/main/tail-land.html');
echo $tail;
?>
