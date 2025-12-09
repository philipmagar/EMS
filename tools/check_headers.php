<?php
// Simple scanner to detect files that may send output before headers
// Place this file at tools/check_headers.php and run with PHP-CLI.

$root = realpath(__DIR__ . '/..');
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

$issues = [];

foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') continue;
    // Skip the tools directory itself when scanning
    if (strpos($path, DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR) !== false) continue;

    $content = file_get_contents($path);
    if ($content === false) continue;

    // 1) Detect UTF-8 BOM at start
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $issues[] = [
            'file' => $path,
            'type' => 'BOM',
            'message' => 'File starts with UTF-8 BOM which causes output before PHP code.'
        ];
        continue;
    }

    // 2) Detect any non-PHP output before the first <?php tag (leading HTML or whitespace)
    if (preg_match('/^\s*<[^?]/s', $content)) {
        // If file is a known header/footer include (common pattern), it's probably intentional.
        $rel = substr($path, strlen($root) + 1);
        // If the file contains session_start() or header() and it appears after the beginning -> warn
        $firstIncludeHeader = preg_match('/include(_once)?\s*\(?\s*["\'\'](?:inlcudes|includes)\/header\.php["\'\']\)?/i', $content);

        if ($firstIncludeHeader) {
            $issues[] = [
                'file' => $path,
                'type' => 'LEADING_HTML',
                'message' => "File contains leading HTML (likely intentional) but includes header.php — make sure you call session_start()/header() before including header.php"
            ];
        } else {
            $issues[] = [
                'file' => $path,
                'type' => 'LEADING_HTML',
                'message' => 'File begins with HTML output before PHP — this will send output before headers.'
            ];
        }
        continue;
    }

    // 3) If a file includes header.php BEFORE calling session_start or header() it may break headers
    if (preg_match('/include(_once)?\s*\(?\s*["\'\'](?:inlcudes|includes)\/header\.php["\'\']\)?/i', $content, $m, PREG_OFFSET_CAPTURE)) {
        $incPos = $m[0][1];
        // Be tolerant to whitespace between function name and parentheses (e.g., session_start (); )
        $sessionPos = PHP_INT_MAX;
        if (preg_match('/session_start\s*\(/i', $content, $sm, PREG_OFFSET_CAPTURE)) {
            $sessionPos = $sm[0][1];
        }
        $headerPos = PHP_INT_MAX;
        if (preg_match('/\bheader\s*\(/i', $content, $hm, PREG_OFFSET_CAPTURE)) {
            $headerPos = $hm[0][1];
        }

        // Only treat session ordering as an issue when the file actually uses sessions
        $usesSession = (bool) preg_match('/\$_SESSION|session_start\s*\(/i', $content);

        // Flag when header() appears after include or when a session is required but session_start() appears after include
        if ($headerPos > $incPos || ($usesSession && $sessionPos > $incPos)) {
            $issues[] = [
                'file' => $path,
                'type' => 'INCLUDE_ORDER',
                'message' => 'This file includes header.php before session_start() or header() — include header after starting session and before sending output.'
            ];
        }
    }

    // 4) Detect accidental BOM-like or whitespace at start (like newline before <?php)
    if (preg_match('/^\s+<\?php/s', $content)) {
        $issues[] = [
            'file' => $path,
            'type' => 'LEADING_WHITESPACE',
            'message' => 'File contains whitespace/newline before opening <?php tag which sends output.'
        ];
    }
}

// Output results
if (empty($issues)) {
    echo "No obvious header/output-before-headers issues detected.\n";
    exit(0);
}

echo "Found potential issues:\n\n";
foreach ($issues as $i) {
    echo $i['file'] . "\n";
    echo "  - type: " . $i['type'] . "\n";
    echo "  - " . $i['message'] . "\n\n";
}

exit(1);

?>