<?php
/**
 * GitHub Markdown Viewer - PHP Version
 * Fetches .md from GitHub and renders as HTML5
 * Usage: view.php?repo=username/repo&file=path/to/file.md
 */
header('Content-Type: text/html; charset=utf-8');

// Configuration
$default_repo = 'sustance/configs/refs/heads';  // Change to your default repo
$default_file = 'test.md';
$github_token = '';  // Optional: if you need private repo access
$cache_time = 300;   // Cache for 5 minutes

// Get parameters
$repo = $_GET['repo'] ?? $default_repo;
$file = $_GET['file'] ?? $default_file;

// Sanitize inputs
$repo = preg_replace('/[^a-zA-Z0-9\/\-_.]/', '', $repo);
$file = preg_replace('/\.\.|[^a-zA-Z0-9\/\-_.]/', '', $file);

// GitHub Raw  https://raw.githubusercontent.com/sustance/configs/refs/heads/main/
$github_url = "https://raw.githubusercontent.com/{$repo}/main/md/{$file}";
//$github_url = "https://raw.githubusercontent.com/sustance/configs/refs/heads/main/md/test.md";

// Simple caching mechanism
$cache_dir = __DIR__ . '/cache/';
$cache_file = $cache_dir . md5($github_url) . '.cache';

// Create cache directory if it doesn't exist
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Check cache
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_time) {
    $markdown = file_get_contents($cache_file);
} else {
    // Fetch from GitHub
    $context = null;
    if ($github_token) {
        $context = stream_context_create([
            'http' => [
                'header' => "Authorization: token {$github_token}\r\n" .
                           "User-Agent: identity2-viewer/1.0\r\n"
            ]
        ]);
    }
    
    $markdown = @file_get_contents($github_url, false, $context);
    
    if ($markdown === false) {
        $markdown = "# Error\n\nCould not fetch file from GitHub.\n\n**URL:** `{$github_url}`\n\n**Try:**\n- Check the repository and file path\n- Ensure the file exists in the `main` branch\n- If private repo, set a GitHub token";
    } else {
        // Save to cache
        file_put_contents($cache_file, $markdown);
    }
}

// Simple Markdown to HTML converter (basic but functional)
function markdownToHtml($text) {
    // Headers
    $text = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $text);
    
    // Bold and italic
    $text = preg_replace('/\*\*\*(.*?)\*\*\*/', '<strong><em>$1</em></strong>', $text);
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    
    // Code blocks
    $text = preg_replace_callback('/```(\w+)?\n(.*?)\n```/s', function($matches) {
        $lang = $matches[1] ?? '';
        $code = htmlspecialchars($matches[2]);
        return '<pre><code class="language-' . $lang . '">' . $code . '</code></pre>';
    }, $text);
    
    // Inline code
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
    
    // Links
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    
    // Lists
    $text = preg_replace('/^- (.*)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    
    // Paragraphs (convert double newline to paragraph)
    $text = preg_replace('/\n\n(.*?)(?=\n\n|$)/s', "\n<p>$1</p>\n", $text);
    
    // Line breaks
    $text = str_replace("\n", '<br>', $text);
    
    return $text;
}

// Convert markdown
$html_content = markdownToHtml($markdown);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity2 <?php echo htmlspecialchars(basename($file)); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }
        article {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { border-bottom: 2px solid #eaeaea; padding-bottom: 10px; }
        h2 { border-bottom: 1px solid #eaeaea; padding-bottom: 5px; }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'SF Mono', Monaco, Consolas, monospace;
            font-size: 0.9em;
        }
        pre {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 3px solid #0366d6;
        }
        pre code { background: none; padding: 0; }
        a { color: #0366d6; text-decoration: none; }
        a:hover { text-decoration: underline; }
        ul { padding-left: 20px; }
        .header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .file-info {
            font-family: monospace;
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin: 20px 0;
        }
        @media (max-width: 600px) {
            body { padding: 10px; }
            article { padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <!--Note this file lives only on Github
        Shell script on Lenovo fetches and runs
        Uploads resulting HTML to freeshell-->
    </div>
    
    <div class="file-info">

<svg
   width="900"
   height="95"
   version="1.1"
   id="svg1"
   sodipodi:docname="head72.svg"
   inkscape:version="1.4.2 (2aeb623e1d, 2025-05-12)"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:svg="http://www.w3.org/2000/svg">
  <defs
     id="defs1" />
  <sodipodi:namedview
     id="namedview1"
     pagecolor="#ffffff"
     bordercolor="#111111"
     borderopacity="1"
     inkscape:showpageshadow="0"
     inkscape:pageopacity="0"
     inkscape:pagecheckerboard="1"
     inkscape:deskcolor="#d1d1d1""
     showgrid="false"
     inkscape:current-layer="svg1"
     showguides="false" />
  <text
     x="-2.5"
     y="73.109375"
     font-family="Verdana"
     font-size="100px"
     fill="rgba(255, 99, 132, 0.1)"
     stroke="#000000"
     stroke-width="1"
     id="text1"
     style="font-size:96px;stroke-width:1.5;stroke-dasharray:none"><tspan
       style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:96px;font-family:Georgia;-inkscape-font-specification:'Georgia Bold';stroke-width:1.5;stroke-dasharray:none"
       id="tspan1">🪶Identity²</tspan></text>
</svg>

        
        <!--
        Repository: <strong><?php echo htmlspecialchars($repo); ?></strong><br>
        File: <strong><?php echo htmlspecialchars($file); ?></strong><br>
        Source: <a href="https://github.com/<?php echo htmlspecialchars($repo); ?>/blob/main/<?php echo htmlspecialchars($file); ?>">View on GitHub</a>
    -->
    </div>
    
    <article>
        <?php echo $html_content; ?>
    </article>

<svg
   width="900"
   height="95"
   version="1.1"
   id="svg1"
   sodipodi:docname="head72.svg"
   inkscape:version="1.4.2 (2aeb623e1d, 2025-05-12)"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:svg="http://www.w3.org/2000/svg">
  <defs
     id="defs1" />
  <sodipodi:namedview
     id="namedview1"
     pagecolor="#ffffff"
     bordercolor="#111111"
     borderopacity="1"
     inkscape:showpageshadow="0"
     inkscape:pageopacity="0"
     inkscape:pagecheckerboard="1"
     inkscape:deskcolor="#d1d1d1""
     showgrid="false"
     inkscape:current-layer="svg1"
     showguides="false" />
  <text
     x="-2.5"
     y="73.109375"
     font-family="Verdana"
     font-size="100px"
     fill="rgba(255, 99, 132, 0.1)"
     stroke="#000000"
     stroke-width="1"
     id="text1"
     style="font-size:96px;stroke-width:1.5;stroke-dasharray:none"><tspan
       style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:96px;font-family:Georgia;-inkscape-font-specification:'Georgia Bold';stroke-width:1.5;stroke-dasharray:none"
       id="tspan1">🪶Identity²</tspan></text>
</svg>



      
    <footer style="margin-top: 30px; text-align: center; color: #666; font-size: 0.9em;">
        <hr>
        <p>Generated by <a href="https://identity2.com">identity2</a> PHP viewer | 
           <a href="?">Refresh</a> | 
           <a href="?repo=<?php echo urlencode($repo); ?>&file=README.md">View README</a>
        </p>
    </footer>
</body>
</html>
