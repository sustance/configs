<?php
// Fetch head section data
$head = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/head-land.html');
echo $head;

// Fetch ServerList section data
import('i-svrlist.php');

<pre>                   C E F O J T    I P
allow_url_fopen    : ENABLED      : DISABLED
allow_url_include  : DISABLED     : DISABLED
curl_extension     : AVAILABLE    : MISSING
write_permissions  : WRITABLE     : WRITABLE
ALL_DYNAMIC_METHODS_FAIL cron fallback 
    G    NO PHP
    H    PHP Fatal error: </pre>

<?php
$tail = file_get_contents('https://raw.githubusercontent.com/sustance/sustance.github.io/refs/heads/main/tail-land.html');
echo $tail;
?>
