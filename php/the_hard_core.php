<?php
// index.php - Hard Science Fiction Directory
// Includes forms for linking submissions and contact/signup

// --- Configuration ---
$site_title = "The Hard Core";
$tagline = "Hard Science Fiction. No Fantasy. No Gatekeeping.";
$contact_email = "identity2+thc@enves.net";
$upload_dir = "submissions/"; // Directory to store form data (optional)

// --- Simple Form Handling (For demonstration - writes to a file) ---
// In production, you'd want to send emails or use a database.
// Ensure the 'submissions' directory exists and is writable.
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$notification = '';
$notification_class = '';

// Handle Link Submission Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'submit_link') {
        $title = htmlspecialchars(trim($_POST['story_title'] ?? ''));
        $author = htmlspecialchars(trim($_POST['author_name'] ?? ''));
        $link = filter_var(trim($_POST['story_link'] ?? ''), FILTER_SANITIZE_URL);
        $type = htmlspecialchars(trim($_POST['link_type'] ?? ''));
        $description = htmlspecialchars(trim($_POST['short_description'] ?? ''));

        if ($title && $author && $link && $type) {
            $entry = "--- Link Submission ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Title: $title\n";
            $entry .= "Author: $author\n";
            $entry .= "Link: $link\n";
            $entry .= "Type: $type\n";
            $entry .= "Description: $description\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'link_submissions.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            $notification = "Thank you! Your link has been submitted for review.";
            $notification_class = 'success';
        } else {
            $notification = "Please fill in all required fields (Title, Author, Link, Type).";
            $notification_class = 'error';
        }
    }

    // Handle Contact / Signup Form
    if ($_POST['form_type'] === 'contact') {
        $name = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
        $email = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

        if ($name && $email && $message) {
            $entry = "--- Contact / Signup ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Name: $name\n";
            $entry .= "Email: $email\n";
            $entry .= "Message: $message\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'contacts.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            // Optional: Send email notification
            // mail($contact_email, "New Contact from $name", $message, "From: $email");
            
            $notification = "Message sent! We'll be in touch.";
            $notification_class = 'success';
        } else {
            $notification = "Please provide a valid name, email, and message.";
            $notification_class = 'error';
        }
    }
}

// --- Static List of Curated Links (Example) ---
// In a real site, these would come from a database or a flat file.
$curated_links = [
    [
        'title' => 'The Living Edge',
        'author' => 'Idel Twobe',
        'url' => 'https://www.royalroad.com/fiction/150031/the-living-edge',
        'type' => 'free',
        'description' => 'Hard science fiction about choosing cultivation over domination, cooperation over division, and the courage to build slowly when the world demands quick fixes. It's a story of three scientists who came to Mars as employees of Earth programs and became architects of Martian independence.'
    ],
    [
        'title' => 'The Thinking Edge',
        'author' => 'IIdel Twobe,
        'url' => '#',
        'type' => 'free',
        'description' => 'Sequel to The Living Edge, not online yet.'
    ],
    [
        'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
        'author' => 'cassioferreira',
        'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
        'type' => 'stub',
        'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
            'author' => 'cassioferreira',
            'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
            'type' => 'search',
            'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Legion; The Many Lives of Stephen Leeds',
            'author' => 'Brandon Sanderson',
            'url' => 'https://www.goodreads.com/book/show/39332065-legion',
            'type' => 'paid',
            'description' => 'A genius of unrivaled aptitude, Stephen can learn any new skill, vocation, or art in a matter of hours. However, to contain all of this, his mind creates hallucinatory people.'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Hard Science Fiction Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0a0c0f;
            color: #e0e4e9;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header / Hero */
        .hero {
            background: linear-gradient(135deg, #0f1217 0%, #1a1f2a 100%);
            border-bottom: 2px solid #2a3b4c;
            padding: 60px 0;
            text-align: center;
        }
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.3rem;
            color: #9aa8b9;
            border-bottom: 1px solid #2a3b4c;
            display: inline-block;
            padding-bottom: 10px;
        }
        .manifesto {
            max-width: 800px;
            margin: 30px auto 0;
            color: #b8c5d4;
            font-size: 1.1rem;
            text-align: left;
            background: #151b24;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #feac6d;
        }
        .manifesto p {
            margin-bottom: 15px;
        }
        .manifesto strong {
            color: #8bb9fe;
        }
        /* Notifications */
        .notification {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 6px;
            font-weight: 500;
        }
        .notification.success {
            background: #1b3b2a;
            border-left: 4px solid #3dd68c;
            color: #b0f0c0;
        }
        .notification.error {
            background: #4a1e2a;
            border-left: 4px solid #f28b82;
            color: #ffd6d0;
        }
        /* Section Headers */
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 50px 0 30px;
            color: #feac6d;
            border-bottom: 1px solid #2a3b4c;
            padding-bottom: 10px;
        }
        .section-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 40px 0 20px;
            color: #8bb9fe;
        }
        /* Cards for Links */
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .link-card {
            background: #151b24;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #2a3b4c;
            transition: transform 0.2s, border-color 0.2s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            border-color: #8bb9fe;
        }
        .link-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .link-title a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .link-title a:hover {
            text-decoration: underline;
        }
        .link-author {
            color: #feac6d;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .link-description {
            color: #b8c5d4;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .link-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-free {
            background: #1b3b2a;
            color: #b0f0c0;
        }
        .type-paid {
            background: #4a3b1a;
            color: #f0d88c;
        }
        /* Forms */
        .form-container {
            background: #151b24;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #2a3b4c;
            margin: 40px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #b8c5d4;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: #1e2630;
            border: 1px solid #2a3b4c;
            border-radius: 4px;
            color: #e0e4e9;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8bb9fe;
        }
        button {
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            color: #0a0c0f;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.9;
        }
        .footer {
            background: #0f1217;
            border-top: 1px solid #2a3b4c;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #6e7e91;
        }
        .footer a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #2a3b4c;
            margin: 30px 0;
        }
        small {
            color: #6e7e91;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><?php echo $site_title; ?></h1>
            <div class="tagline"><?php echo $tagline; ?></div>
            <div class="manifesto">
                <p>You've read the classics. You know your Asimov from your Clarke. But the modern market is a flood of dragons, magic systems, and space operas that ignore the laws of thermodynamics.</p>
                <p>I read the science news every day. When I hear they're launching fuel reserves into orbit, I don't just think it's cool—I think about the boil-off. The cryogenics. The nightmare of direct sunlight.</p>
                <p><strong>That speculation is the core of a story.</strong> This site is a stepping-off point for <strong>Modern Speculative Hard Sci-Fi.</strong></p>
                <p style="margin-bottom:0;">We are agnostic. Viva la AI. Viva la human. Viva la story. If the premise is solid and the science is respected, it belongs here.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Notification Area -->
        <?php if ($notification): ?>
        <div class="notification <?php echo $notification_class; ?>">
            <?php echo $notification; ?>
        </div>
        <?php endif; ?>

        <!-- Directory Section (Curated Links) -->
        <h2 class="section-title">The Directory</h2>
        <p style="margin-bottom:20px; color:#b8c5d4;">Hand-picked hard science fiction that respects the math. Updated regularly.</p>
        
        <div class="link-grid">
            <?php foreach ($curated_links as $link): ?>
            <div class="link-card">
                <div class="link-title"><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($link['title']); ?></a></div>
                <div class="link-author">by <?php echo htmlspecialchars($link['author']); ?></div>
                <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                <span class="link-type <?php echo $link['type'] === 'free' ? 'type-free' : 'type-paid'; ?>">
                    <?php echo $link['type'] === 'free' ? 'Free' : 'Paid'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:right;"><a href="#" style="color:#8bb9fe;">Browse all links →</a></p>

        <hr>

        <!-- Submission Form: Link Your Story -->
        <h2 class="section-subtitle" id="submit">Submit Your Story</h2>
        <p>Have a story that belongs here? Drop the link below. Human-written, AI-assisted, or AI-originated—all are welcome as long as it's hard sci-fi, not fantasy.</p>
        
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="submit_link">
                
                <div class="form-group">
                    <label for="story_title">Story Title *</label>
                    <input type="text" id="story_title" name="story_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_name">Author/Creator Name *</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                
                <div class="form-group">
                    <label for="story_link">Link to Story (URL) *</label>
                    <input type="url" id="story_link" name="story_link" required placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="link_type">Type *</label>
                    <select id="link_type" name="link_type" required>
                        <option value="">-- Select --</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description (optional, but recommended)</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="What's the core speculative idea?"></textarea>
                </div>
                
                <button type="submit">Submit Link</button>
                <small style="display:block; margin-top:10px;">We'll review and add it to the directory. Your info won't be shared.</small>
            </form>
        </div>

        <hr><?php
// index.php - Hard Science Fiction Directory
// Includes forms for linking submissions and contact/signup

// --- Configuration ---
$site_title = "The Hard Core";
$tagline = "Hard Science Fiction. No Fantasy. No Gatekeeping.";
$contact_email = "identity2+thc@enves.net";
$upload_dir = "submissions/"; // Directory to store form data (optional)

// --- Simple Form Handling (For demonstration - writes to a file) ---
// In production, you'd want to send emails or use a database.
// Ensure the 'submissions' directory exists and is writable.
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$notification = '';
$notification_class = '';

// Handle Link Submission Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'submit_link') {
        $title = htmlspecialchars(trim($_POST['story_title'] ?? ''));
        $author = htmlspecialchars(trim($_POST['author_name'] ?? ''));
        $link = filter_var(trim($_POST['story_link'] ?? ''), FILTER_SANITIZE_URL);
        $type = htmlspecialchars(trim($_POST['link_type'] ?? ''));
        $description = htmlspecialchars(trim($_POST['short_description'] ?? ''));

        if ($title && $author && $link && $type) {
            $entry = "--- Link Submission ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Title: $title\n";
            $entry .= "Author: $author\n";
            $entry .= "Link: $link\n";
            $entry .= "Type: $type\n";
            $entry .= "Description: $description\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'link_submissions.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            $notification = "Thank you! Your link has been submitted for review.";
            $notification_class = 'success';
        } else {
            $notification = "Please fill in all required fields (Title, Author, Link, Type).";
            $notification_class = 'error';
        }
    }

    // Handle Contact / Signup Form
    if ($_POST['form_type'] === 'contact') {
        $name = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
        $email = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

        if ($name && $email && $message) {
            $entry = "--- Contact / Signup ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Name: $name\n";
            $entry .= "Email: $email\n";
            $entry .= "Message: $message\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'contacts.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            // Optional: Send email notification
            // mail($contact_email, "New Contact from $name", $message, "From: $email");
            
            $notification = "Message sent! We'll be in touch.";
            $notification_class = 'success';
        } else {
            $notification = "Please provide a valid name, email, and message.";
            $notification_class = 'error';
        }
    }
}

// --- Static List of Curated Links (Example) ---
// In a real site, these would come from a database or a flat file.
$curated_links = [
    [
        'title' => 'The Living Edge',
        'author' => 'Idel Twobe',
        'url' => 'https://www.royalroad.com/fiction/150031/the-living-edge',
        'type' => 'free',
        'description' => 'Hard science fiction about choosing cultivation over domination, cooperation over division, and the courage to build slowly when the world demands quick fixes. It's a story of three scientists who came to Mars as employees of Earth programs and became architects of Martian independence.'
    ],
    [
        'title' => 'The Thinking Edge',
        'author' => 'IIdel Twobe,
        'url' => '#',
        'type' => 'free',
        'description' => 'Sequel to The Living Edge, not online yet.'
    ],
    [
        'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
        'author' => 'cassioferreira',
        'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
        'type' => 'stub',
        'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
            'author' => 'cassioferreira',
            'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
            'type' => 'search',
            'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Legion; The Many Lives of Stephen Leeds',
            'author' => 'Brandon Sanderson',
            'url' => 'https://www.goodreads.com/book/show/39332065-legion',
            'type' => 'paid',
            'description' => 'A genius of unrivaled aptitude, Stephen can learn any new skill, vocation, or art in a matter of hours. However, to contain all of this, his mind creates hallucinatory people.'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Hard Science Fiction Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0a0c0f;
            color: #e0e4e9;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header / Hero */
        .hero {
            background: linear-gradient(135deg, #0f1217 0%, #1a1f2a 100%);
            border-bottom: 2px solid #2a3b4c;
            padding: 60px 0;
            text-align: center;
        }
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.3rem;
            color: #9aa8b9;
            border-bottom: 1px solid #2a3b4c;
            display: inline-block;
            padding-bottom: 10px;
        }
        .manifesto {
            max-width: 800px;
            margin: 30px auto 0;
            color: #b8c5d4;
            font-size: 1.1rem;
            text-align: left;
            background: #151b24;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #feac6d;
        }
        .manifesto p {
            margin-bottom: 15px;
        }
        .manifesto strong {
            color: #8bb9fe;
        }
        /* Notifications */
        .notification {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 6px;
            font-weight: 500;
        }
        .notification.success {
            background: #1b3b2a;
            border-left: 4px solid #3dd68c;
            color: #b0f0c0;
        }
        .notification.error {
            background: #4a1e2a;
            border-left: 4px solid #f28b82;
            color: #ffd6d0;
        }
        /* Section Headers */
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 50px 0 30px;
            color: #feac6d;
            border-bottom: 1px solid #2a3b4c;
            padding-bottom: 10px;
        }
        .section-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 40px 0 20px;
            color: #8bb9fe;
        }
        /* Cards for Links */
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .link-card {
            background: #151b24;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #2a3b4c;
            transition: transform 0.2s, border-color 0.2s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            border-color: #8bb9fe;
        }
        .link-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .link-title a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .link-title a:hover {
            text-decoration: underline;
        }
        .link-author {
            color: #feac6d;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .link-description {
            color: #b8c5d4;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .link-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-free {
            background: #1b3b2a;
            color: #b0f0c0;
        }
        .type-paid {
            background: #4a3b1a;
            color: #f0d88c;
        }
        /* Forms */
        .form-container {
            background: #151b24;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #2a3b4c;
            margin: 40px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #b8c5d4;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: #1e2630;
            border: 1px solid #2a3b4c;
            border-radius: 4px;
            color: #e0e4e9;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8bb9fe;
        }
        button {
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            color: #0a0c0f;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.9;
        }
        .footer {
            background: #0f1217;
            border-top: 1px solid #2a3b4c;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #6e7e91;
        }
        .footer a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #2a3b4c;
            margin: 30px 0;
        }
        small {
            color: #6e7e91;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><?php echo $site_title; ?></h1>
            <div class="tagline"><?php echo $tagline; ?></div>
            <div class="manifesto">
                <p>You've read the classics. You know your Asimov from your Clarke. But the modern market is a flood of dragons, magic systems, and space operas that ignore the laws of thermodynamics.</p>
                <p>I read the science news every day. When I hear they're launching fuel reserves into orbit, I don't just think it's cool—I think about the boil-off. The cryogenics. The nightmare of direct sunlight.</p>
                <p><strong>That speculation is the core of a story.</strong> This site is a stepping-off point for <strong>Modern Speculative Hard Sci-Fi.</strong></p>
                <p style="margin-bottom:0;">We are agnostic. Viva la AI. Viva la human. Viva la story. If the premise is solid and the science is respected, it belongs here.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Notification Area -->
        <?php if ($notification): ?>
        <div class="notification <?php echo $notification_class; ?>">
            <?php echo $notification; ?>
        </div>
        <?php endif; ?>

        <!-- Directory Section (Curated Links) -->
        <h2 class="section-title">The Directory</h2>
        <p style="margin-bottom:20px; color:#b8c5d4;">Hand-picked hard science fiction that respects the math. Updated regularly.</p>
        
        <div class="link-grid">
            <?php foreach ($curated_links as $link): ?>
            <div class="link-card">
                <div class="link-title"><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($link['title']); ?></a></div>
                <div class="link-author">by <?php echo htmlspecialchars($link['author']); ?></div>
                <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                <span class="link-type <?php echo $link['type'] === 'free' ? 'type-free' : 'type-paid'; ?>">
                    <?php echo $link['type'] === 'free' ? 'Free' : 'Paid'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:right;"><a href="#" style="color:#8bb9fe;">Browse all links →</a></p>

        <hr>

        <!-- Submission Form: Link Your Story -->
        <h2 class="section-subtitle" id="submit">Submit Your Story</h2>
        <p>Have a story that belongs here? Drop the link below. Human-written, AI-assisted, or AI-originated—all are welcome as long as it's hard sci-fi, not fantasy.</p>
        
        <div class="form-container"><?php
// index.php - Hard Science Fiction Directory
// Includes forms for linking submissions and contact/signup

// --- Configuration ---
$site_title = "The Hard Core";
$tagline = "Hard Science Fiction. No Fantasy. No Gatekeeping.";
$contact_email = "identity2+thc@enves.net";
$upload_dir = "submissions/"; // Directory to store form data (optional)

// --- Simple Form Handling (For demonstration - writes to a file) ---
// In production, you'd want to send emails or use a database.
// Ensure the 'submissions' directory exists and is writable.
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$notification = '';
$notification_class = '';

// Handle Link Submission Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'submit_link') {
        $title = htmlspecialchars(trim($_POST['story_title'] ?? ''));
        $author = htmlspecialchars(trim($_POST['author_name'] ?? ''));
        $link = filter_var(trim($_POST['story_link'] ?? ''), FILTER_SANITIZE_URL);
        $type = htmlspecialchars(trim($_POST['link_type'] ?? ''));
        $description = htmlspecialchars(trim($_POST['short_description'] ?? ''));

        if ($title && $author && $link && $type) {
            $entry = "--- Link Submission ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Title: $title\n";
            $entry .= "Author: $author\n";
            $entry .= "Link: $link\n";
            $entry .= "Type: $type\n";
            $entry .= "Description: $description\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'link_submissions.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            $notification = "Thank you! Your link has been submitted for review.";
            $notification_class = 'success';
        } else {
            $notification = "Please fill in all required fields (Title, Author, Link, Type).";
            $notification_class = 'error';
        }
    }

    // Handle Contact / Signup Form
    if ($_POST['form_type'] === 'contact') {
        $name = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
        $email = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

        if ($name && $email && $message) {
            $entry = "--- Contact / Signup ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Name: $name\n";
            $entry .= "Email: $email\n";
            $entry .= "Message: $message\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'contacts.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            // Optional: Send email notification
            // mail($contact_email, "New Contact from $name", $message, "From: $email");
            
            $notification = "Message sent! We'll be in touch.";
            $notification_class = 'success';
        } else {
            $notification = "Please provide a valid name, email, and message.";
            $notification_class = 'error';
        }
    }
}

// --- Static List of Curated Links (Example) ---
// In a real site, these would come from a database or a flat file.
$curated_links = [
    [
        'title' => 'The Living Edge',
        'author' => 'Idel Twobe',
        'url' => 'https://www.royalroad.com/fiction/150031/the-living-edge',
        'type' => 'free',
        'description' => 'Hard science fiction about choosing cultivation over domination, cooperation over division, and the courage to build slowly when the world demands quick fixes. It's a story of three scientists who came to Mars as employees of Earth programs and became architects of Martian independence.'
    ],
    [
        'title' => 'The Thinking Edge',
        'author' => 'IIdel Twobe,
        'url' => '#',
        'type' => 'free',
        'description' => 'Sequel to The Living Edge, not online yet.'
    ],
    [
        'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
        'author' => 'cassioferreira',
        'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
        'type' => 'stub',
        'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
            'author' => 'cassioferreira',
            'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
            'type' => 'search',
            'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Legion; The Many Lives of Stephen Leeds',
            'author' => 'Brandon Sanderson',
            'url' => 'https://www.goodreads.com/book/show/39332065-legion',
            'type' => 'paid',
            'description' => 'A genius of unrivaled aptitude, Stephen can learn any new skill, vocation, or art in a matter of hours. However, to contain all of this, his mind creates hallucinatory people.'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Hard Science Fiction Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0a0c0f;
            color: #e0e4e9;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header / Hero */
        .hero {
            background: linear-gradient(135deg, #0f1217 0%, #1a1f2a 100%);
            border-bottom: 2px solid #2a3b4c;
            padding: 60px 0;
            text-align: center;
        }
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.3rem;
            color: #9aa8b9;
            border-bottom: 1px solid #2a3b4c;
            display: inline-block;
            padding-bottom: 10px;
        }
        .manifesto {
            max-width: 800px;
            margin: 30px auto 0;
            color: #b8c5d4;
            font-size: 1.1rem;
            text-align: left;
            background: #151b24;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #feac6d;
        }
        .manifesto p {
            margin-bottom: 15px;
        }
        .manifesto strong {
            color: #8bb9fe;<?php
// index.php - Hard Science Fiction Directory
// Includes forms for linking submissions and contact/signup

// --- Configuration ---
$site_title = "The Hard Core";
$tagline = "Hard Science Fiction. No Fantasy. No Gatekeeping.";
$contact_email = "identity2+thc@enves.net";
$upload_dir = "submissions/"; // Directory to store form data (optional)

// --- Simple Form Handling (For demonstration - writes to a file) ---
// In production, you'd want to send emails or use a database.
// Ensure the 'submissions' directory exists and is writable.
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$notification = '';
$notification_class = '';

// Handle Link Submission Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'submit_link') {
        $title = htmlspecialchars(trim($_POST['story_title'] ?? ''));
        $author = htmlspecialchars(trim($_POST['author_name'] ?? ''));
        $link = filter_var(trim($_POST['story_link'] ?? ''), FILTER_SANITIZE_URL);
        $type = htmlspecialchars(trim($_POST['link_type'] ?? ''));
        $description = htmlspecialchars(trim($_POST['short_description'] ?? ''));

        if ($title && $author && $link && $type) {
            $entry = "--- Link Submission ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Title: $title\n";
            $entry .= "Author: $author\n";
            $entry .= "Link: $link\n";
            $entry .= "Type: $type\n";
            $entry .= "Description: $description\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'link_submissions.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            $notification = "Thank you! Your link has been submitted for review.";
            $notification_class = 'success';
        } else {
            $notification = "Please fill in all required fields (Title, Author, Link, Type).";
            $notification_class = 'error';
        }
    }

    // Handle Contact / Signup Form
    if ($_POST['form_type'] === 'contact') {
        $name = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
        $email = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

        if ($name && $email && $message) {
            $entry = "--- Contact / Signup ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Name: $name\n";
            $entry .= "Email: $email\n";
            $entry .= "Message: $message\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'contacts.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            // Optional: Send email notification
            // mail($contact_email, "New Contact from $name", $message, "From: $email");
            
            $notification = "Message sent! We'll be in touch.";
            $notification_class = 'success';
        } else {
            $notification = "Please provide a valid name, email, and message.";
            $notification_class = 'error';
        }
    }
}

// --- Static List of Curated Links (Example) ---
// In a real site, these would come from a database or a flat file.
$curated_links = [
    [
        'title' => 'The Living Edge',
        'author' => 'Idel Twobe',
        'url' => 'https://www.royalroad.com/fiction/150031/the-living-edge',
        'type' => 'free',
        'description' => 'Hard science fiction about choosing cultivation over domination, cooperation over division, and the courage to build slowly when the world demands quick fixes. It's a story of three scientists who came to Mars as employees of Earth programs and became architects of Martian independence.'
    ],
    [
        'title' => 'The Thinking Edge',
        'author' => 'IIdel Twobe,
        'url' => '#',
        'type' => 'free',
        'description' => 'Sequel to The Living Edge, not online yet.'
    ],
    [
        'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
        'author' => 'cassioferreira',
        'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
        'type' => 'stub',
        'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
            'author' => 'cassioferreira',
            'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
            'type' => 'search',
            'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Legion; The Many Lives of Stephen Leeds',
            'author' => 'Brandon Sanderson',
            'url' => 'https://www.goodreads.com/book/show/39332065-legion',
            'type' => 'paid',
            'description' => 'A genius of unrivaled aptitude, Stephen can learn any new skill, vocation, or art in a matter of hours. However, to contain all of this, his mind creates hallucinatory people.'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Hard Science Fiction Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0a0c0f;
            color: #e0e4e9;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header / Hero */
        .hero {
            background: linear-gradient(135deg, #0f1217 0%, #1a1f2a 100%);
            border-bottom: 2px solid #2a3b4c;
            padding: 60px 0;
            text-align: center;
        }
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.3rem;
            color: #9aa8b9;
            border-bottom: 1px solid #2a3b4c;
            display: inline-block;
            padding-bottom: 10px;
        }
        .manifesto {
            max-width: 800px;
            margin: 30px auto 0;
            color: #b8c5d4;
            font-size: 1.1rem;
            text-align: left;
            background: #151b24;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #feac6d;
        }
        .manifesto p {
            margin-bottom: 15px;
        }
        .manifesto strong {
            color: #8bb9fe;
        }
        /* Notifications */
        .notification {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 6px;
            font-weight: 500;
        }
        .notification.success {
            background: #1b3b2a;
            border-left: 4px solid #3dd68c;
            color: #b0f0c0;
        }
        .notification.error {
            background: #4a1e2a;
            border-left: 4px solid #f28b82;
            color: #ffd6d0;
        }
        /* Section Headers */
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 50px 0 30px;
            color: #feac6d;
            border-bottom: 1px solid #2a3b4c;
            padding-bottom: 10px;
        }
        .section-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 40px 0 20px;<?php
// index.php - Hard Science Fiction Directory
// Includes forms for linking submissions and contact/signup

// --- Configuration ---
$site_title = "The Hard Core";
$tagline = "Hard Science Fiction. No Fantasy. No Gatekeeping.";
$contact_email = "identity2+thc@enves.net";
$upload_dir = "submissions/"; // Directory to store form data (optional)

// --- Simple Form Handling (For demonstration - writes to a file) ---
// In production, you'd want to send emails or use a database.
// Ensure the 'submissions' directory exists and is writable.
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$notification = '';
$notification_class = '';

// Handle Link Submission Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'submit_link') {
        $title = htmlspecialchars(trim($_POST['story_title'] ?? ''));
        $author = htmlspecialchars(trim($_POST['author_name'] ?? ''));
        $link = filter_var(trim($_POST['story_link'] ?? ''), FILTER_SANITIZE_URL);
        $type = htmlspecialchars(trim($_POST['link_type'] ?? ''));
        $description = htmlspecialchars(trim($_POST['short_description'] ?? ''));

        if ($title && $author && $link && $type) {
            $entry = "--- Link Submission ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Title: $title\n";
            $entry .= "Author: $author\n";
            $entry .= "Link: $link\n";
            $entry .= "Type: $type\n";
            $entry .= "Description: $description\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'link_submissions.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            $notification = "Thank you! Your link has been submitted for review.";
            $notification_class = 'success';
        } else {
            $notification = "Please fill in all required fields (Title, Author, Link, Type).";
            $notification_class = 'error';
        }
    }

    // Handle Contact / Signup Form
    if ($_POST['form_type'] === 'contact') {
        $name = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
        $email = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

        if ($name && $email && $message) {
            $entry = "--- Contact / Signup ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Name: $name\n";
            $entry .= "Email: $email\n";
            $entry .= "Message: $message\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'contacts.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            // Optional: Send email notification
            // mail($contact_email, "New Contact from $name", $message, "From: $email");
            
            $notification = "Message sent! We'll be in touch.";
            $notification_class = 'success';
        } else {
            $notification = "Please provide a valid name, email, and message.";
            $notification_class = 'error';
        }
    }
}

// --- Static List of Curated Links (Example) ---
// In a real site, these would come from a database or a flat file.
$curated_links = [
    [
        'title' => 'The Living Edge',
        'author' => 'Idel Twobe',
        'url' => 'https://www.royalroad.com/fiction/150031/the-living-edge',
        'type' => 'free',
        'description' => 'Hard science fiction about choosing cultivation over domination, cooperation over division, and the courage to build slowly when the world demands quick fixes. It's a story of three scientists who came to Mars as employees of Earth programs and became architects of Martian independence.'
    ],
    [
        'title' => 'The Thinking Edge',
        'author' => 'IIdel Twobe,
        'url' => '#',
        'type' => 'free',
        'description' => 'Sequel to The Living Edge, not online yet.'
    ],
    [
        'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
        'author' => 'cassioferreira',
        'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
        'type' => 'stub',
        'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
            'author' => 'cassioferreira',
            'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
            'type' => 'search',
            'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Legion; The Many Lives of Stephen Leeds',
            'author' => 'Brandon Sanderson',
            'url' => 'https://www.goodreads.com/book/show/39332065-legion',
            'type' => 'paid',
            'description' => 'A genius of unrivaled aptitude, Stephen can learn any new skill, vocation, or art in a matter of hours. However, to contain all of this, his mind creates hallucinatory people.'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Hard Science Fiction Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0a0c0f;
            color: #e0e4e9;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header / Hero */
        .hero {
            background: linear-gradient(135deg, #0f1217 0%, #1a1f2a 100%);
            border-bottom: 2px solid #2a3b4c;
            padding: 60px 0;
            text-align: center;
        }
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.3rem;
            color: #9aa8b9;
            border-bottom: 1px solid #2a3b4c;
            display: inline-block;
            padding-bottom: 10px;
        }
        .manifesto {
            max-width: 800px;
            margin: 30px auto 0;
            color: #b8c5d4;
            font-size: 1.1rem;
            text-align: left;
            background: #151b24;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #feac6d;
        }
        .manifesto p {
            margin-bottom: 15px;
        }
        .manifesto strong {
            color: #8bb9fe;
        }
        /* Notifications */
        .notification {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 6px;
            font-weight: 500;
        }
        .notification.success {
            background: #1b3b2a;
            border-left: 4px solid #3dd68c;
            color: #b0f0c0;
        }
        .notification.error {
            background: #4a1e2a;
            border-left: 4px solid #f28b82;
            color: #ffd6d0;
        }
        /* Section Headers */
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 50px 0 30px;
            color: #feac6d;
            border-bottom: 1px solid #2a3b4c;
            padding-bottom: 10px;
        }
        .section-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 40px 0 20px;
            color: #8bb9fe;
        }
        /* Cards for Links */
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .link-card {
            background: #151b24;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #2a3b4c;
            transition: transform 0.2s, border-color 0.2s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            border-color: #8bb9fe;
        }
        .link-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .link-title a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .link-title a:hover {
            text-decoration: underline;
        }
        .link-author {
            color: #feac6d;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .link-description {
            color: #b8c5d4;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .link-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-free {
            background: #1b3b2a;
            color: #b0f0c0;
        }
        .type-paid {
            background: #4a3b1a;
            color: #f0d88c;
        }
        /* Forms */
        .form-container {
            background: #151b24;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #2a3b4c;
            margin: 40px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #b8c5d4;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: #1e2630;
            border: 1px solid #2a3b4c;
            border-radius: 4px;
            color: #e0e4e9;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8bb9fe;
        }
        button {
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            color: #0a0c0f;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.9;
        }
        .footer {
            background: #0f1217;
            border-top: 1px solid #2a3b4c;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #6e7e91;
        }
        .footer a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #2a3b4c;
            margin: 30px 0;
        }
        small {
            color: #6e7e91;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><?php echo $site_title; ?></h1>
            <div class="tagline"><?php echo $tagline; ?></div>
            <div class="manifesto">
                <p>You've read the classics. You know your Asimov from your Clarke. But the modern market is a flood of dragons, magic systems, and space operas that ignore the laws of thermodynamics.</p>
                <p>I read the science news every day. When I hear they're launching fuel reserves into orbit, I don't just think it's cool—I think about the boil-off. The cryogenics. The nightmare of direct sunlight.</p>
                <p><strong>That speculation is the core of a story.</strong> This site is a stepping-off point for <strong>Modern Speculative Hard Sci-Fi.</strong></p>
                <p style="margin-bottom:0;">We are agnostic. Viva la AI. Viva la human. Viva la story. If the premise is solid and the science is respected, it belongs here.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Notification Area -->
        <?php if ($notification): ?>
        <div class="notification <?php echo $notification_class; ?>">
            <?php echo $notification; ?>
        </div>
        <?php endif; ?>

        <!-- Directory Section (Curated Links) -->
        <h2 class="section-title">The Directory</h2>
        <p style="margin-bottom:20px; color:#b8c5d4;">Hand-picked hard science fiction that respects the math. Updated regularly.</p>
        
        <div class="link-grid">
            <?php foreach ($curated_links as $link): ?>
            <div class="link-card">
                <div class="link-title"><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($link['title']); ?></a></div>
                <div class="link-author">by <?php echo htmlspecialchars($link['author']); ?></div>
                <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                <span class="link-type <?php echo $link['type'] === 'free' ? 'type-free' : 'type-paid'; ?>">
                    <?php echo $link['type'] === 'free' ? 'Free' : 'Paid'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:right;"><a href="#" style="color:#8bb9fe;">Browse all links →</a></p>

        <hr>

        <!-- Submission Form: Link Your Story -->
        <h2 class="section-subtitle" id="submit">Submit Your Story</h2>
<?php
// index.php - Hard Science Fiction Directory
// Includes forms for linking submissions and contact/signup

// --- Configuration ---
$site_title = "The Hard Core";
$tagline = "Hard Science Fiction. No Fantasy. No Gatekeeping.";
$contact_email = "identity2+thc@enves.net";
$upload_dir = "submissions/"; // Directory to store form data (optional)

// --- Simple Form Handling (For demonstration - writes to a file) ---
// In production, you'd want to send emails or use a database.
// Ensure the 'submissions' directory exists and is writable.
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$notification = '';
$notification_class = '';

// Handle Link Submission Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'submit_link') {
        $title = htmlspecialchars(trim($_POST['story_title'] ?? ''));
        $author = htmlspecialchars(trim($_POST['author_name'] ?? ''));
        $link = filter_var(trim($_POST['story_link'] ?? ''), FILTER_SANITIZE_URL);
        $type = htmlspecialchars(trim($_POST['link_type'] ?? ''));
        $description = htmlspecialchars(trim($_POST['short_description'] ?? ''));

        if ($title && $author && $link && $type) {
            $entry = "--- Link Submission ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Title: $title\n";
            $entry .= "Author: $author\n";
            $entry .= "Link: $link\n";
            $entry .= "Type: $type\n";
            $entry .= "Description: $description\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'link_submissions.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            $notification = "Thank you! Your link has been submitted for review.";
            $notification_class = 'success';
        } else {
            $notification = "Please fill in all required fields (Title, Author, Link, Type).";
            $notification_class = 'error';
        }
    }

    // Handle Contact / Signup Form
    if ($_POST['form_type'] === 'contact') {
        $name = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
        $email = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

        if ($name && $email && $message) {
            $entry = "--- Contact / Signup ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Name: $name\n";
            $entry .= "Email: $email\n";
            $entry .= "Message: $message\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'contacts.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            // Optional: Send email notification
            // mail($contact_email, "New Contact from $name", $message, "From: $email");
            
            $notification = "Message sent! We'll be in touch.";
            $notification_class = 'success';
        } else {
            $notification = "Please provide a valid name, email, and message.";
            $notification_class = 'error';
        }
    }
}

// --- Static List of Curated Links (Example) ---
// In a real site, these would come from a database or a flat file.
$curated_links = [
    [
        'title' => 'The Living Edge',
        'author' => 'Idel Twobe',
        'url' => 'https://www.royalroad.com/fiction/150031/the-living-edge',
        'type' => 'free',
        'description' => 'Hard science fiction about choosing cultivation over domination, cooperation over division, and the courage to build slowly when the world demands quick fixes. It's a story of three scientists who came to Mars as employees of Earth programs and became architects of Martian independence.'
    ],
    [
        'title' => 'The Thinking Edge',
        'author' => 'IIdel Twobe,
        'url' => '#',
        'type' => 'free',
        'description' => 'Sequel to The Living Edge, not online yet.'
    ],
    [
        'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
        'author' => 'cassioferreira',
        'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
        'type' => 'stub',
        'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
            'author' => 'cassioferreira',
            'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
            'type' => 'search',
            'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Legion; The Many Lives of Stephen Leeds',
            'author' => 'Brandon Sanderson',
            'url' => 'https://www.goodreads.com/book/show/39332065-legion',
            'type' => 'paid',
            'description' => 'A genius of unrivaled aptitude, Stephen can learn any new skill, vocation, or art in a matter of hours. However, to contain all of this, his mind creates hallucinatory people.'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Hard Science Fiction Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0a0c0f;
            color: #e0e4e9;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header / Hero */
        .hero {
            background: linear-gradient(135deg, #0f1217 0%, #1a1f2a 100%);
            border-bottom: 2px solid #2a3b4c;
            padding: 60px 0;
            text-align: center;
        }
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.3rem;
            color: #9aa8b9;
            border-bottom: 1px solid #2a3b4c;
            display: inline-block;
            padding-bottom: 10px;
        }
        .manifesto {
            max-width: 800px;
            margin: 30px auto 0;
            color: #b8c5d4;
            font-size: 1.1rem;
            text-align: left;
            background: #151b24;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #feac6d;
        }
        .manifesto p {
            margin-bottom: 15px;
        }
        .manifesto strong {
            color: #8bb9fe;
        }
        /* Notifications */
        .notification {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 6px;
            font-weight: 500;
        }
        .notification.success {
            background: #1b3b2a;
            border-left: 4px solid #3dd68c;
            color: #b0f0c0;
        }
        .notification.error {
            background: #4a1e2a;
            border-left: 4px solid #f28b82;
            color: #ffd6d0;
        }
        /* Section Headers */
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 50px 0 30px;
            color: #feac6d;
            border-bottom: 1px solid #2a3b4c;
            padding-bottom: 10px;
        }
        .section-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 40px 0 20px;
            color: #8bb9fe;
        }
        /* Cards for Links */
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .link-card {
            background: #151b24;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #2a3b4c;
            transition: transform 0.2s, border-color 0.2s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            border-color: #8bb9fe;
        }
        .link-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .link-title a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .link-title a:hover {
            text-decoration: underline;
        }
        .link-author {
            color: #feac6d;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .link-description {
            color: #b8c5d4;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .link-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-free {
            background: #1b3b2a;
            color: #b0f0c0;
        }
        .type-paid {
            background: #4a3b1a;
            color: #f0d88c;
        }
        /* Forms */
        .form-container {
            background: #151b24;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #2a3b4c;
            margin: 40px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #b8c5d4;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: #1e2630;
            border: 1px solid #2a3b4c;
            border-radius: 4px;
            color: #e0e4e9;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8bb9fe;
        }
        button {
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            color: #0a0c0f;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.9;
        }
        .footer {
            background: #0f1217;
            border-top: 1px solid #2a3b4c;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #6e7e91;
        }
        .footer a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #2a3b4c;
            margin: 30px 0;
        }
        small {
            color: #6e7e91;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><?php echo $site_title; ?></h1>
            <div class="tagline"><?php echo $tagline; ?></div>
            <div class="manifesto">
                <p>You've read the classics. You know your Asimov from your Clarke. But the modern market is a flood of dragons, magic systems, and space operas that ignore the laws of thermodynamics.</p>
                <p>I read the science news every day. When I hear they're launching fuel reserves into orbit, I don't just think it's cool—I think about the boil-off. The cryogenics. The nightmare of direct sunlight.</p>
                <p><strong>That speculation is the core of a story.</strong> This site is a stepping-off point for <strong>Modern Speculative Hard Sci-Fi.</strong></p>
                <p style="margin-bottom:0;">We are agnostic. Viva la AI. Viva la human. Viva la story. If the premise is solid and the science is respected, it belongs here.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Notification Area -->
        <?php if ($notification): ?>
        <div class="notification <?php echo $notification_class; ?>">
            <?php echo $notification; ?>
        </div>
        <?php endif; ?>

        <!-- Directory Section (Curated Links) -->
        <h2 class="section-title">The Directory</h2>
        <p style="margin-bottom:20px; color:#b8c5d4;">Hand-picked hard science fiction that respects the math. Updated regularly.</p>
        
        <div class="link-grid">
            <?php foreach ($curated_links as $link): ?>
            <div class="link-card">
                <div class="link-title"><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($link['title']); ?></a></div>
                <div class="link-author">by <?php echo htmlspecialchars($link['author']); ?></div>
                <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                <span class="link-type <?php echo $<?php
// index.php - Hard Science Fiction Directory
// Includes forms for linking submissions and contact/signup

// --- Configuration ---
$site_title = "The Hard Core";
$tagline = "Hard Science Fiction. No Fantasy. No Gatekeeping.";
$contact_email = "identity2+thc@enves.net";
$upload_dir = "submissions/"; // Directory to store form data (optional)

// --- Simple Form Handling (For demonstration - writes to a file) ---
// In production, you'd want to send emails or use a database.
// Ensure the 'submissions' directory exists and is writable.
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$notification = '';
$notification_class = '';

// Handle Link Submission Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    if ($_POST['form_type'] === 'submit_link') {
        $title = htmlspecialchars(trim($_POST['story_title'] ?? ''));
        $author = htmlspecialchars(trim($_POST['author_name'] ?? ''));
        $link = filter_var(trim($_POST['story_link'] ?? ''), FILTER_SANITIZE_URL);
        $type = htmlspecialchars(trim($_POST['link_type'] ?? ''));
        $description = htmlspecialchars(trim($_POST['short_description'] ?? ''));

        if ($title && $author && $link && $type) {
            $entry = "--- Link Submission ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Title: $title\n";
            $entry .= "Author: $author\n";
            $entry .= "Link: $link\n";
            $entry .= "Type: $type\n";
            $entry .= "Description: $description\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'link_submissions.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            $notification = "Thank you! Your link has been submitted for review.";
            $notification_class = 'success';
        } else {
            $notification = "Please fill in all required fields (Title, Author, Link, Type).";
            $notification_class = 'error';
        }
    }

    // Handle Contact / Signup Form
    if ($_POST['form_type'] === 'contact') {
        $name = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
        $email = filter_var(trim($_POST['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $message = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

        if ($name && $email && $message) {
            $entry = "--- Contact / Signup ---\n";
            $entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
            $entry .= "Name: $name\n";
            $entry .= "Email: $email\n";
            $entry .= "Message: $message\n";
            $entry .= "------------------------\n\n";

            $file = $upload_dir . 'contacts.txt';
            file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
            
            // Optional: Send email notification
            // mail($contact_email, "New Contact from $name", $message, "From: $email");
            
            $notification = "Message sent! We'll be in touch.";
            $notification_class = 'success';
        } else {
            $notification = "Please provide a valid name, email, and message.";
            $notification_class = 'error';
        }
    }
}

// --- Static List of Curated Links (Example) ---
// In a real site, these would come from a database or a flat file.
$curated_links = [
    [
        'title' => 'The Living Edge',
        'author' => 'Idel Twobe',
        'url' => 'https://www.royalroad.com/fiction/150031/the-living-edge',
        'type' => 'free',
        'description' => 'Hard science fiction about choosing cultivation over domination, cooperation over division, and the courage to build slowly when the world demands quick fixes. It's a story of three scientists who came to Mars as employees of Earth programs and became architects of Martian independence.'
    ],
    [
        'title' => 'The Thinking Edge',
        'author' => 'IIdel Twobe,
        'url' => '#',
        'type' => 'free',
        'description' => 'Sequel to The Living Edge, not online yet.'
    ],
    [
        'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
        'author' => 'cassioferreira',
        'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
        'type' => 'stub',
        'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Planet Ignis [Dystopian; Hard Sci-Fi]',
            'author' => 'cassioferreira',
            'url' => 'https://www.royalroad.com/fiction/68700/planet-ignis-dystopian-hard-sci-fi',
            'type' => 'search',
            'description' => 'A space opera for fans of strange alien societies, pyrokinetic mutants, and chess. The settlers of planet Ignis are barely making it. .'
    ],
    [
            'title' => 'Legion; The Many Lives of Stephen Leeds',
            'author' => 'Brandon Sanderson',
            'url' => 'https://www.goodreads.com/book/show/39332065-legion',
            'type' => 'paid',
            'description' => 'A genius of unrivaled aptitude, Stephen can learn any new skill, vocation, or art in a matter of hours. However, to contain all of this, his mind creates hallucinatory people.'
    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_title; ?> - Hard Science Fiction Directory</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #0a0c0f;
            color: #e0e4e9;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Header / Hero */
        .hero {
            background: linear-gradient(135deg, #0f1217 0%, #1a1f2a 100%);
            border-bottom: 2px solid #2a3b4c;
            padding: 60px 0;
            text-align: center;
        }
        h1 {
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.3rem;
            color: #9aa8b9;
            border-bottom: 1px solid #2a3b4c;
            display: inline-block;
            padding-bottom: 10px;
        }
        .manifesto {
            max-width: 800px;
            margin: 30px auto 0;
            color: #b8c5d4;
            font-size: 1.1rem;
            text-align: left;
            background: #151b24;
            padding: 30px;
            border-radius: 8px;
            border-left: 4px solid #feac6d;
        }
        .manifesto p {
            margin-bottom: 15px;
        }
        .manifesto strong {
            color: #8bb9fe;
        }
        /* Notifications */
        .notification {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 6px;
            font-weight: 500;
        }
        .notification.success {
            background: #1b3b2a;
            border-left: 4px solid #3dd68c;
            color: #b0f0c0;
        }
        .notification.error {
            background: #4a1e2a;
            border-left: 4px solid #f28b82;
            color: #ffd6d0;
        }
        /* Section Headers */
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 50px 0 30px;
            color: #feac6d;
            border-bottom: 1px solid #2a3b4c;
            padding-bottom: 10px;
        }
        .section-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 40px 0 20px;
            color: #8bb9fe;
        }
        /* Cards for Links */
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .link-card {
            background: #151b24;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #2a3b4c;
            transition: transform 0.2s, border-color 0.2s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            border-color: #8bb9fe;
        }
        .link-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .link-title a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .link-title a:hover {
            text-decoration: underline;
        }
        .link-author {
            color: #feac6d;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .link-description {
            color: #b8c5d4;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .link-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-free {
            background: #1b3b2a;
            color: #b0f0c0;
        }
        .type-paid {
            background: #4a3b1a;
            color: #f0d88c;
        }
        /* Forms */
        .form-container {
            background: #151b24;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #2a3b4c;
            margin: 40px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #b8c5d4;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: #1e2630;
            border: 1px solid #2a3b4c;
            border-radius: 4px;
            color: #e0e4e9;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8bb9fe;
        }
        button {
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            color: #0a0c0f;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.9;
        }
        .footer {
            background: #0f1217;
            border-top: 1px solid #2a3b4c;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #6e7e91;
        }
        .footer a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #2a3b4c;
            margin: 30px 0;
        }
        small {
            color: #6e7e91;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><?php echo $site_title; ?></h1>
            <div class="tagline"><?php echo $tagline; ?></div>
            <div class="manifesto">
                <p>You've read the classics. You know your Asimov from your Clarke. But the modern market is a flood of dragons, magic systems, and space operas that ignore the laws of thermodynamics.</p>
                <p>I read the science news every day. When I hear they're launching fuel reserves into orbit, I don't just think it's cool—I think about the boil-off. The cryogenics. The nightmare of direct sunlight.</p>
                <p><strong>That speculation is the core of a story.</strong> This site is a stepping-off point for <strong>Modern Speculative Hard Sci-Fi.</strong></p>
                <p style="margin-bottom:0;">We are agnostic. Viva la AI. Viva la human. Viva la story. If the premise is solid and the science is respected, it belongs here.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Notification Area -->
        <?php if ($notification): ?>
        <div class="notification <?php echo $notification_class; ?>">
            <?php echo $notification; ?>
        </div>
        <?php endif; ?>

        <!-- Directory Section (Curated Links) -->
        <h2 class="section-title">The Directory</h2>
        <p style="margin-bottom:20px; color:#b8c5d4;">Hand-picked hard science fiction that respects the math. Updated regularly.</p>
        
        <div class="link-grid">
            <?php foreach ($curated_links as $link): ?>
            <div class="link-card">
                <div class="link-title"><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($link['title']); ?></a></div>
                <div class="link-author">by <?php echo htmlspecialchars($link['author']); ?></div>
                <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                <span class="link-type <?php echo $link['type'] === 'free' ? 'type-free' : 'type-paid'; ?>">
                    <?php echo $link['type'] === 'free' ? 'Free' : 'Paid'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:right;"><a href="#" style="color:#8bb9fe;">Browse all links →</a></p>

        <hr>

        <!-- Submission Form: Link Your Story -->
        <h2 class="section-subtitle" id="submit">Submit Your Story</h2>
        <p>Have a story that belongs here? Drop the link below. Human-written, AI-assisted, or AI-originated—all are welcome as long as it's hard sci-fi, not fantasy.</p>
        
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="submit_link">
                
                <div class="form-group">
                    <label for="story_title">Story Title *</label>
                    <input type="text" id="story_title" name="story_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_name">Author/Creator Name *</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                
                <div class="form-group">
                    <label for="story_link">Link to Story (URL) *</label>
                    <input type="url" id="story_link" name="story_link" required placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="link_type">Type *</label>
                    <select id="link_type" name="link_type" required>
                        <option value="">-- Select --</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description (optional, but recommended)</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="What's the core speculative idea?"></textarea>
                </div>
                
                <button type="submit">Submit Link</button>
                <small style="display:block; margin-top:10px;">We'll review and add it to the directory. Your info won't be shared.</small>
            </form>
        </div>

        <hr>

        <!-- Contact / Signup Form -->
        <h2 class="section-subtitle" id="contact">Stay in the Loop</h2>
        <p>Questions, suggestions, or just want to chat about orbital mechanics and speculative fiction? Drop us a line.</p>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="contact">
                
                <div class="form-group">
                    <label for="contact_name">Your Name *</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message">Message / Signup Note *</label>
                    <textarea id="contact_message" name="contact_message" rows="4" required placeholder="Tell us what you're working on, or just say hi..."></textarea>
                </div>
                
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>
                <strong><?php echo $site_title; ?></strong> — Built for the minds that ask, "Yes, but how do we keep it in the shade?"
            </p>
            <p style="margin-top:15px;">
                <a href="#submit">Submit a Link</a> &nbsp;|&nbsp; <a href="#contact">Contact</a> &nbsp;|&nbsp; <a href="#">Browse Directory</a>
            </p>
            <p style="margin-top:20px; font-size:0.9rem;">
                © <?php echo date('Y'); ?> • Totally agnostic about tools. Viva la story.
            </p>
        </div>
    </div>
</body>
</html>
link['type'] === 'free' ? 'type-free' : 'type-paid'; ?>">
                    <?php echo $link['type'] === 'free' ? 'Free' : 'Paid'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:right;"><a href="#" style="color:#8bb9fe;">Browse all links →</a></p>

        <hr>

        <!-- Submission Form: Link Your Story -->
        <h2 class="section-subtitle" id="submit">Submit Your Story</h2>
        <p>Have a story that belongs here? Drop the link below. Human-written, AI-assisted, or AI-originated—all are welcome as long as it's hard sci-fi, not fantasy.</p>
        
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="submit_link">
                
                <div class="form-group">
                    <label for="story_title">Story Title *</label>
                    <input type="text" id="story_title" name="story_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_name">Author/Creator Name *</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                
                <div class="form-group">
                    <label for="story_link">Link to Story (URL) *</label>
                    <input type="url" id="story_link" name="story_link" required placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="link_type">Type *</label>
                    <select id="link_type" name="link_type" required>
                        <option value="">-- Select --</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description (optional, but recommended)</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="What's the core speculative idea?"></textarea>
                </div>
                
                <button type="submit">Submit Link</button>
                <small style="display:block; margin-top:10px;">We'll review and add it to the directory. Your info won't be shared.</small>
            </form>
        </div>

        <hr>

        <!-- Contact / Signup Form -->
        <h2 class="section-subtitle" id="contact">Stay in the Loop</h2>
        <p>Questions, suggestions, or just want to chat about orbital mechanics and speculative fiction? Drop us a line.</p>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="contact">
                
                <div class="form-group">
                    <label for="contact_name">Your Name *</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message">Message / Signup Note *</label>
                    <textarea id="contact_message" name="contact_message" rows="4" required placeholder="Tell us what you're working on, or just say hi..."></textarea>
                </div>
                
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>
                <strong><?php echo $site_title; ?></strong> — Built for the minds that ask, "Yes, but how do we keep it in the shade?"
            </p>
            <p style="margin-top:15px;">
                <a href="#submit">Submit a Link</a> &nbsp;|&nbsp; <a href="#contact">Contact</a> &nbsp;|&nbsp; <a href="#">Browse Directory</a>
            </p>
            <p style="margin-top:20px; font-size:0.9rem;">
                © <?php echo date('Y'); ?> • Totally agnostic about tools. Viva la story.
            </p>
        </div>
    </div>
</body>
</html>
        <p>Have a story that belongs here? Drop the link below. Human-written, AI-assisted, or AI-originated—all are welcome as long as it's hard sci-fi, not fantasy.</p>
        
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="submit_link">
                
                <div class="form-group">
                    <label for="story_title">Story Title *</label>
                    <input type="text" id="story_title" name="story_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_name">Author/Creator Name *</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                
                <div class="form-group">
                    <label for="story_link">Link to Story (URL) *</label>
                    <input type="url" id="story_link" name="story_link" required placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="link_type">Type *</label>
                    <select id="link_type" name="link_type" required>
                        <option value="">-- Select --</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description (optional, but recommended)</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="What's the core speculative idea?"></textarea>
                </div>
                
                <button type="submit">Submit Link</button>
                <small style="display:block; margin-top:10px;">We'll review and add it to the directory. Your info won't be shared.</small>
            </form>
        </div>

        <hr>

        <!-- Contact / Signup Form -->
        <h2 class="section-subtitle" id="contact">Stay in the Loop</h2>
        <p>Questions, suggestions, or just want to chat about orbital mechanics and speculative fiction? Drop us a line.</p>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="contact">
                
                <div class="form-group">
                    <label for="contact_name">Your Name *</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message">Message / Signup Note *</label>
                    <textarea id="contact_message" name="contact_message" rows="4" required placeholder="Tell us what you're working on, or just say hi..."></textarea>
                </div>
                
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>
                <strong><?php echo $site_title; ?></strong> — Built for the minds that ask, "Yes, but how do we keep it in the shade?"
            </p>
            <p style="margin-top:15px;">
                <a href="#submit">Submit a Link</a> &nbsp;|&nbsp; <a href="#contact">Contact</a> &nbsp;|&nbsp; <a href="#">Browse Directory</a>
            </p>
            <p style="margin-top:20px; font-size:0.9rem;">
                © <?php echo date('Y'); ?> • Totally agnostic about tools. Viva la story.
            </p>
        </div>
    </div>
</body>
</html>

            color: #8bb9fe;
        }
        /* Cards for Links */
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .link-card {
            background: #151b24;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #2a3b4c;
            transition: transform 0.2s, border-color 0.2s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            border-color: #8bb9fe;
        }
        .link-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .link-title a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .link-title a:hover {
            text-decoration: underline;
        }
        .link-author {
            color: #feac6d;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .link-description {
            color: #b8c5d4;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .link-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-free {
            background: #1b3b2a;
            color: #b0f0c0;
        }
        .type-paid {
            background: #4a3b1a;
            color: #f0d88c;
        }
        /* Forms */
        .form-container {
            background: #151b24;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #2a3b4c;
            margin: 40px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #b8c5d4;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: #1e2630;
            border: 1px solid #2a3b4c;
            border-radius: 4px;
            color: #e0e4e9;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8bb9fe;
        }
        button {
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            color: #0a0c0f;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.9;
        }
        .footer {
            background: #0f1217;
            border-top: 1px solid #2a3b4c;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #6e7e91;
        }
        .footer a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #2a3b4c;
            margin: 30px 0;
        }
        small {
            color: #6e7e91;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><?php echo $site_title; ?></h1>
            <div class="tagline"><?php echo $tagline; ?></div>
            <div class="manifesto">
                <p>You've read the classics. You know your Asimov from your Clarke. But the modern market is a flood of dragons, magic systems, and space operas that ignore the laws of thermodynamics.</p>
                <p>I read the science news every day. When I hear they're launching fuel reserves into orbit, I don't just think it's cool—I think about the boil-off. The cryogenics. The nightmare of direct sunlight.</p>
                <p><strong>That speculation is the core of a story.</strong> This site is a stepping-off point for <strong>Modern Speculative Hard Sci-Fi.</strong></p>
                <p style="margin-bottom:0;">We are agnostic. Viva la AI. Viva la human. Viva la story. If the premise is solid and the science is respected, it belongs here.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Notification Area -->
        <?php if ($notification): ?>
        <div class="notification <?php echo $notification_class; ?>">
            <?php echo $notification; ?>
        </div>
        <?php endif; ?>

        <!-- Directory Section (Curated Links) -->
        <h2 class="section-title">The Directory</h2>
        <p style="margin-bottom:20px; color:#b8c5d4;">Hand-picked hard science fiction that respects the math. Updated regularly.</p>
        
        <div class="link-grid">
            <?php foreach ($curated_links as $link): ?>
            <div class="link-card">
                <div class="link-title"><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($link['title']); ?></a></div>
                <div class="link-author">by <?php echo htmlspecialchars($link['author']); ?></div>
                <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                <span class="link-type <?php echo $link['type'] === 'free' ? 'type-free' : 'type-paid'; ?>">
                    <?php echo $link['type'] === 'free' ? 'Free' : 'Paid'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:right;"><a href="#" style="color:#8bb9fe;">Browse all links →</a></p>

        <hr>

        <!-- Submission Form: Link Your Story -->
        <h2 class="section-subtitle" id="submit">Submit Your Story</h2>
        <p>Have a story that belongs here? Drop the link below. Human-written, AI-assisted, or AI-originated—all are welcome as long as it's hard sci-fi, not fantasy.</p>
        
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="submit_link">
                
                <div class="form-group">
                    <label for="story_title">Story Title *</label>
                    <input type="text" id="story_title" name="story_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_name">Author/Creator Name *</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                
                <div class="form-group">
                    <label for="story_link">Link to Story (URL) *</label>
                    <input type="url" id="story_link" name="story_link" required placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="link_type">Type *</label>
                    <select id="link_type" name="link_type" required>
                        <option value="">-- Select --</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description (optional, but recommended)</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="What's the core speculative idea?"></textarea>
                </div>
                
                <button type="submit">Submit Link</button>
                <small style="display:block; margin-top:10px;">We'll review and add it to the directory. Your info won't be shared.</small>
            </form>
        </div>

        <hr>

        <!-- Contact / Signup Form -->
        <h2 class="section-subtitle" id="contact">Stay in the Loop</h2>
        <p>Questions, suggestions, or just want to chat about orbital mechanics and speculative fiction? Drop us a line.</p>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="contact">
                
                <div class="form-group">
                    <label for="contact_name">Your Name *</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message">Message / Signup Note *</label>
                    <textarea id="contact_message" name="contact_message" rows="4" required placeholder="Tell us what you're working on, or just say hi..."></textarea>
                </div>
                
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>
                <strong><?php echo $site_title; ?></strong> — Built for the minds that ask, "Yes, but how do we keep it in the shade?"
            </p>
            <p style="margin-top:15px;">
                <a href="#submit">Submit a Link</a> &nbsp;|&nbsp; <a href="#contact">Contact</a> &nbsp;|&nbsp; <a href="#">Browse Directory</a>
            </p>
            <p style="margin-top:20px; font-size:0.9rem;">
                © <?php echo date('Y'); ?> • Totally agnostic about tools. Viva la story.
            </p>
        </div>
    </div>
</body>
</html>

        }
        /* Notifications */
        .notification {
            padding: 15px 20px;
            margin: 20px auto;
            max-width: 800px;
            border-radius: 6px;
            font-weight: 500;
        }
        .notification.success {
            background: #1b3b2a;
            border-left: 4px solid #3dd68c;
            color: #b0f0c0;
        }
        .notification.error {
            background: #4a1e2a;
            border-left: 4px solid #f28b82;
            color: #ffd6d0;
        }
        /* Section Headers */
        .section-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 50px 0 30px;
            color: #feac6d;
            border-bottom: 1px solid #2a3b4c;
            padding-bottom: 10px;
        }
        .section-subtitle {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 40px 0 20px;
            color: #8bb9fe;
        }
        /* Cards for Links */
        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .link-card {
            background: #151b24;
            border-radius: 8px;
            padding: 25px;
            border: 1px solid #2a3b4c;
            transition: transform 0.2s, border-color 0.2s;
        }
        .link-card:hover {
            transform: translateY(-3px);
            border-color: #8bb9fe;
        }
        .link-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .link-title a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .link-title a:hover {
            text-decoration: underline;
        }
        .link-author {
            color: #feac6d;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .link-description {
            color: #b8c5d4;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .link-type {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .type-free {
            background: #1b3b2a;
            color: #b0f0c0;
        }
        .type-paid {
            background: #4a3b1a;
            color: #f0d88c;
        }
        /* Forms */
        .form-container {
            background: #151b24;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #2a3b4c;
            margin: 40px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #b8c5d4;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            background: #1e2630;
            border: 1px solid #2a3b4c;
            border-radius: 4px;
            color: #e0e4e9;
            font-size: 1rem;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #8bb9fe;
        }
        button {
            background: linear-gradient(135deg, #8bb9fe 0%, #feac6d 100%);
            color: #0a0c0f;
            font-weight: 600;
            font-size: 1rem;
            padding: 14px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover {
            opacity: 0.9;
        }
        .footer {
            background: #0f1217;
            border-top: 1px solid #2a3b4c;
            padding: 40px 0;
            margin-top: 60px;
            text-align: center;
            color: #6e7e91;
        }
        .footer a {
            color: #8bb9fe;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #2a3b4c;
            margin: 30px 0;
        }
        small {
            color: #6e7e91;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1><?php echo $site_title; ?></h1>
            <div class="tagline"><?php echo $tagline; ?></div>
            <div class="manifesto">
                <p>You've read the classics. You know your Asimov from your Clarke. But the modern market is a flood of dragons, magic systems, and space operas that ignore the laws of thermodynamics.</p>
                <p>I read the science news every day. When I hear they're launching fuel reserves into orbit, I don't just think it's cool—I think about the boil-off. The cryogenics. The nightmare of direct sunlight.</p>
                <p><strong>That speculation is the core of a story.</strong> This site is a stepping-off point for <strong>Modern Speculative Hard Sci-Fi.</strong></p>
                <p style="margin-bottom:0;">We are agnostic. Viva la AI. Viva la human. Viva la story. If the premise is solid and the science is respected, it belongs here.</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Notification Area -->
        <?php if ($notification): ?>
        <div class="notification <?php echo $notification_class; ?>">
            <?php echo $notification; ?>
        </div>
        <?php endif; ?>

        <!-- Directory Section (Curated Links) -->
        <h2 class="section-title">The Directory</h2>
        <p style="margin-bottom:20px; color:#b8c5d4;">Hand-picked hard science fiction that respects the math. Updated regularly.</p>
        
        <div class="link-grid">
            <?php foreach ($curated_links as $link): ?>
            <div class="link-card">
                <div class="link-title"><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($link['title']); ?></a></div>
                <div class="link-author">by <?php echo htmlspecialchars($link['author']); ?></div>
                <div class="link-description"><?php echo htmlspecialchars($link['description']); ?></div>
                <span class="link-type <?php echo $link['type'] === 'free' ? 'type-free' : 'type-paid'; ?>">
                    <?php echo $link['type'] === 'free' ? 'Free' : 'Paid'; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:right;"><a href="#" style="color:#8bb9fe;">Browse all links →</a></p>

        <hr>

        <!-- Submission Form: Link Your Story -->
        <h2 class="section-subtitle" id="submit">Submit Your Story</h2>
        <p>Have a story that belongs here? Drop the link below. Human-written, AI-assisted, or AI-originated—all are welcome as long as it's hard sci-fi, not fantasy.</p>
        
        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="submit_link">
                
                <div class="form-group">
                    <label for="story_title">Story Title *</label>
                    <input type="text" id="story_title" name="story_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_name">Author/Creator Name *</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                
                <div class="form-group">
                    <label for="story_link">Link to Story (URL) *</label>
                    <input type="url" id="story_link" name="story_link" required placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="link_type">Type *</label>
                    <select id="link_type" name="link_type" required>
                        <option value="">-- Select --</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description (optional, but recommended)</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="What's the core speculative idea?"></textarea>
                </div>
                
                <button type="submit">Submit Link</button>
                <small style="display:block; margin-top:10px;">We'll review and add it to the directory. Your info won't be shared.</small>
            </form>
        </div>

        <hr>

        <!-- Contact / Signup Form -->
        <h2 class="section-subtitle" id="contact">Stay in the Loop</h2>
        <p>Questions, suggestions, or just want to chat about orbital mechanics and speculative fiction? Drop us a line.</p>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="contact">
                
                <div class="form-group">
                    <label for="contact_name">Your Name *</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message">Message / Signup Note *</label>
                    <textarea id="contact_message" name="contact_message" rows="4" required placeholder="Tell us what you're working on, or just say hi..."></textarea>
                </div>
                
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>
                <strong><?php echo $site_title; ?></strong> — Built for the minds that ask, "Yes, but how do we keep it in the shade?"
            </p>
            <p style="margin-top:15px;">
                <a href="#submit">Submit a Link</a> &nbsp;|&nbsp; <a href="#contact">Contact</a> &nbsp;|&nbsp; <a href="#">Browse Directory</a>
            </p>
            <p style="margin-top:20px; font-size:0.9rem;">
                © <?php echo date('Y'); ?> • Totally agnostic about tools. Viva la story.
            </p>
        </div>
    </div>
</body>
</html>

            <form method="POST" action="">
                <input type="hidden" name="form_type" value="submit_link">
                
                <div class="form-group">
                    <label for="story_title">Story Title *</label>
                    <input type="text" id="story_title" name="story_title" required>
                </div>
                
                <div class="form-group">
                    <label for="author_name">Author/Creator Name *</label>
                    <input type="text" id="author_name" name="author_name" required>
                </div>
                
                <div class="form-group">
                    <label for="story_link">Link to Story (URL) *</label>
                    <input type="url" id="story_link" name="story_link" required placeholder="https://...">
                </div>
                
                <div class="form-group">
                    <label for="link_type">Type *</label>
                    <select id="link_type" name="link_type" required>
                        <option value="">-- Select --</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="short_description">Short Description (optional, but recommended)</label>
                    <textarea id="short_description" name="short_description" rows="3" placeholder="What's the core speculative idea?"></textarea>
                </div>
                
                <button type="submit">Submit Link</button>
                <small style="display:block; margin-top:10px;">We'll review and add it to the directory. Your info won't be shared.</small>
            </form>
        </div>

        <hr>

        <!-- Contact / Signup Form -->
        <h2 class="section-subtitle" id="contact">Stay in the Loop</h2>
        <p>Questions, suggestions, or just want to chat about orbital mechanics and speculative fiction? Drop us a line.</p>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="contact">
                
                <div class="form-group">
                    <label for="contact_name">Your Name *</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message">Message / Signup Note *</label>
                    <textarea id="contact_message" name="contact_message" rows="4" required placeholder="Tell us what you're working on, or just say hi..."></textarea>
                </div>
                
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>
                <strong><?php echo $site_title; ?></strong> — Built for the minds that ask, "Yes, but how do we keep it in the shade?"
            </p>
            <p style="margin-top:15px;">
                <a href="#submit">Submit a Link</a> &nbsp;|&nbsp; <a href="#contact">Contact</a> &nbsp;|&nbsp; <a href="#">Browse Directory</a>
            </p>
            <p style="margin-top:20px; font-size:0.9rem;">
                © <?php echo date('Y'); ?> • Totally agnostic about tools. Viva la story.
            </p>
        </div>
    </div>
</body>
</html>


        <!-- Contact / Signup Form -->
        <h2 class="section-subtitle" id="contact">Stay in the Loop</h2>
        <p>Questions, suggestions, or just want to chat about orbital mechanics and speculative fiction? Drop us a line.</p>

        <div class="form-container">
            <form method="POST" action="">
                <input type="hidden" name="form_type" value="contact">
                
                <div class="form-group">
                    <label for="contact_name">Your Name *</label>
                    <input type="text" id="contact_name" name="contact_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Your Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_message">Message / Signup Note *</label>
                    <textarea id="contact_message" name="contact_message" rows="4" required placeholder="Tell us what you're working on, or just say hi..."></textarea>
                </div>
                
                <button type="submit">Send Message</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <p>
                <strong><?php echo $site_title; ?></strong> — Built for the minds that ask, "Yes, but how do we keep it in the shade?"
            </p>
            <p style="margin-top:15px;">
                <a href="#submit">Submit a Link</a> &nbsp;|&nbsp; <a href="#contact">Contact</a> &nbsp;|&nbsp; <a href="#">Browse Directory</a>
            </p>
            <p style="margin-top:20px; font-size:0.9rem;">
                © <?php echo date('Y'); ?> • Totally agnostic about tools. Viva la story.
            </p>
        </div>
    </div>
</body>
</html>
