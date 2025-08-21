<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simplified Responsive Page</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>

    <div class="container">
        <nav>
            <ul>
                <?php
                $files = glob('*.{php,htm}', GLOB_BRACE);
                foreach($files as $file) {
                    $name = pathinfo($file, PATHINFO_FILENAME);
                    echo "<li><a href='$file'>$name</a></li>";
                }
                ?>
            </ul>
        </nav>
        <div class="content">
        
            <img src="http://identity2.com/bg2.webp" alt="" height="60%" width="80%">
<a href="http://identity2.com/bg2.webp">Image</a>
