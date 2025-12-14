<!DOCTYPE html>
<html>

<head>
<style>
:root {

/* The standard charts.css set */

  --color-1: rgba(255, 99, 132, 0.75);    /* #ff6384 */
      --col10-1: rgba(255, 99, 132, 1.0);   
      --colo2-1: rgba(255, 99, 132, 0.2); 
  --color-2: rgba(54, 162, 235, 0.75);    /* #36a2eb */
      --col10-2: rgba(54, 162, 235, 1.0); 
      --colo2-2: rgba(54, 162, 235, 0.2);    
  --color-3: rgba(255, 206, 86, 0.75);
      --col10-3: rgba(255, 206, 86, 1.0);
      --colo2-3: rgba(255, 206, 86, 0.2);
  --color-4: rgba(75, 192, 192, 0.75);    /* #4bc0c0 */
      --col10-4: rgba(75, 192, 192, 1.0); 
      --colo2-4: rgba(75, 192, 192, 0.2);
  --color-5: rgba(153, 102, 255, 0.75);   /* #9966ff */
      --col10-5: rgba(153, 102, 255, 1.0); 
      --colo2-5: rgba(153, 102, 255, 0.2);
  --color-6: rgba(255, 159, 64, 0.75);    /* #ff9f40 */
      --col10-6: rgba(255, 159, 64, 1.0); 
      --colo2-6: rgba(255, 159, 64, 0.2); 
  --color-7: rgba(93, 173, 226, 0.75);    /* #5dade2 */
      --colo2-7: rgba(93, 173, 226, 0.2);
      --col10-7: rgba(93, 173, 226, 1.0);
  --color-8: rgba(178, 223, 138, 0.75);   /* #b2df8a */
      --col10-8: rgba(178, 223, 138, 1.0);
      --colo2-8: rgba(178, 223, 138, 0.2);
  --color-9: rgba(255, 117, 117, 0.75);   /* #ff7575 */
      --col10-9: rgba(255, 117, 117, 1.0);
      --colo2-9: rgba(255, 117, 117, 0.2);
  --color-10:rgba(169, 169, 169, 0.75);   /* #a9a9a9 */
      --col10-10:rgba(169, 169, 169, 1.0); 
      --colo2-10:rgba(169, 169, 169, 0.2); 
}

</style>
</head>

<body style="background-color: var(--color-10);">
<?php
//$bot = 'botframe';
//$top = 'topframe';
?>

<?php
 $os = strtolower(trim(php_uname('s')));
 $user = 'unknown'; // Default fallback value

// Get the numeric user ID of the file owner
 $owner_uid = fileowner(__FILE__);

// Check if the required function exists and the lookup is successful
if (function_exists('posix_getpwuid') && $owner_uid !== false) {
    $user_info = posix_getpwuid($owner_uid);
    if ($user_info && isset($user_info['name'])) {
        // Get the username and convert it to lowercase
        $user = strtolower(trim($user_info['name']));
    }
}
//echo "Operating System: " . $os . "\n";
//echo "Script Owner: " . $user . "\n";
?>



	

<div style="border-radius: 255px  15px 255px  15px / 233px  22px  99px 255px;
	background: linear-gradient(to bottom right, var(--col10-2), var(--col10-4) );
	font-weight: 900; color: white;
	">
	<div style="width: 85%; margin: 0 auto;">

<?php
$code = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/banner.php');
//$code = file_get_contents('banner.php');

if ($code === false) {
    die('Cannot load banners');
}

// Execute the code in isolation and capture the returned value
$banners = eval('?>' . $code); 

if (!is_array($banners)) {
    die('Banner file did not return an array');
}

//echo "<pre>" . htmlspecialchars(identity2) . "</pre>";
echo "<pre>" . htmlspecialchars($banners[$user]) . "</pre>";
?>

<?php
$local = file_get_contents('siteSpecific1.php');
echo "$local";
?>
<p><br>THIS SITE IS POWERED BY:</p>
<?php
$code = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/php/banner.php');
//$code = file_get_contents('banner.php');

if ($code === false) {
    die('Cannot load banners');
}

// Execute the code in isolation and capture the returned value
$banners = eval('?>' . $code); 

if (!is_array($banners)) {
    die('Banner file did not return an array');
}

echo "<pre>" . htmlspecialchars($banners[$os]) . "</pre>";
?>

<br><br>
</div>
</div>
  </body>
</html>
