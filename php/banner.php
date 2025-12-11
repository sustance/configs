<?php
// Load YAML parser if not already available
// Note: Requires yaml extension or Symfony YAML component
// If using Symfony YAML: composer require symfony/yaml

// Method 1: Using PHP's yaml extension (if installed)
function displayNetBSDBanner() {
    // Read the YAML file
    $yamlContent = file_get_contents('https://raw.githubusercontent.com/sustance/configs/refs/heads/main/textbanner.yaml');
    
    if ($yamlContent === false) {
        echo "Error: Could not read textbanner.yaml file";
        return;
    }
    
    // Parse YAML
    $data = yaml_parse($yamlContent);
    
    if ($data === false) {
        echo "Error: Could not parse YAML file";
        return;
    }
    
    // Check if netbsd banner exists
    if (isset($data['textbanner']['freebsd'])) {
        return $data['textbanner']['freebsd'];
    } else {
        return "Error: freebsd banner not found in YAML file";
    }
}

// Method 2: Alternative using Symfony YAML component
// If you have Symfony YAML installed via composer
/*
use Symfony\Component\Yaml\Yaml;

function displayNetBSDBannerSymfony() {
    try {
        $data = Yaml::parseFile('textbanner.yaml');
        
        if (isset($data['textbanner']['netbsd'])) {
            return $data['textbanner']['netbsd'];
        } else {
            return "Error: netbsd banner not found in YAML file";
        }
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}
*/

// Method 3: Simple approach - parse manually for this specific format
function displayNetBSDBannerSimple() {
    $content = file_get_contents('textbanner.yaml');
    
    if ($content === false) {
        return "Error: Could not read textbanner.yaml file";
    }
    
    // Look for the required section
    $pattern = '/netbsd:\|([\s\S]*?)(?=\n\w+:\||\n\s*$)/';
    
    if (preg_match($pattern, $content, $matches)) {
        return trim($matches[1]);
    } else {
        return "Error: correct banner not found in YAML file";
    }
}

// Choose which method to use based on your environment
// Uncomment the method you want to use:

// Method 1 (requires yaml extension):
// if (function_exists('yaml_parse')) {
//     $banner = displayNetBSDBanner();
// } else {
//     $banner = "YAML extension not installed. Please install yaml extension or use alternative method.";
// }

// Method 3 (works without extensions):
$banner = displayNetBSDBannerSimple();

// Method 2 (requires Symfony YAML):
// $banner = displayNetBSDBannerSymfony();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetBSD Banner Display</title>
    <style>
        body {
            background-color: #000;
            color: #fff;
            font-family: monospace;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .banner-container {
            background-color: #111;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #333;
            white-space: pre;
            overflow-x: auto;
            max-width: 100%;
        }
        
        .title {
            color: #ff6600;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
        
        pre {
            margin: 0;
            line-height: 1;
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            pre {
                font-size: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="title">NetBSD Banner</div>
    
    <div class="banner-container">
        <pre><?php echo htmlspecialchars($banner); ?></pre>
    </div>
    
    <div class="footer">
        Displaying banner from textbanner.yaml | Generated: <?php echo date('Y-m-d H:i:s'); ?>
    </div>
</body>
</html>
