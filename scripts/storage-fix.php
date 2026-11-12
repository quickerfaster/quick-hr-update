<?php

/**
 * Storage Fix Script (corrected paths)
 * ------------------------------------
 * The previous storage-setup.php ran from the document root and created
 * directories/symlinks in the wrong location. This script detects the
 * correct Laravel base path from index.php and fixes everything.
 *
 * Upload to the document root and access via:
 *   https://agriwatts.quickerfaster.com/storage-fix.php
 *
 * Delete after use.
 */

set_time_limit(30);

// --- Detect paths ---
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__FILE__);
$indexPhp = $docRoot . '/index.php';

echo '<!DOCTYPE html><html><head><title>Storage Fix</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0}h2{color:#00d4ff}.ok{color:#00ff88}.fail{color:#ff4466}.warn{color:#ffaa00}pre{background:#0d0d1a;padding:10px;border-radius:4px}</style>';
echo '</head><body><h1>🛠 Storage Fix (corrected)</h1>';

echo '<p>Document root: <code>' . $docRoot . '</code></p>';

// Read index.php to find the Laravel base path
if (!file_exists($indexPhp)) {
    die('<p class="fail">❌ index.php not found at ' . $indexPhp . '</p>');
}

$indexContent = file_get_contents($indexPhp);

// Look for patterns like: __DIR__.'/../../quick_hr/...'
if (preg_match("#__DIR__\s*\.\s*'([^']+)vendor/autoload\.php'#", $indexContent, $m)) {
    $relative = $m[1];
    $laravelBase = realpath($docRoot . '/' . $relative) ?: ($docRoot . '/' . rtrim($relative, '/'));
    echo '<p>Laravel base (from index.php): <code>' . $laravelBase . '</code></p>';
} else {
    // Fallback: try common patterns
    $laravelBase = dirname($docRoot) . '/quick_hr'; // /home/quickerf/quick_hr
    echo '<p class="warn">⚠️ Could not auto-detect Laravel path from index.php.</p>';
    echo '<p>Guessed: <code>' . $laravelBase . '</code></p>';
}

$storagePublic = $laravelBase . '/storage/app/public';
$wrongStorage  = dirname($docRoot) . '/storage';        // /home/quickerf/public_html/storage
$wrongPublic   = dirname($docRoot) . '/public/storage'; // /home/quickerf/public_html/public/storage

// --- 1. Clean up wrong paths from previous script ---
echo '<h2>1. Cleaning up wrong paths</h2>';

$toClean = [$wrongStorage, $wrongPublic];
foreach ($toClean as $path) {
    if (file_exists($path)) {
        if (is_link($path)) {
            unlink($path);
            echo '<span class="ok">✅</span> Removed wrong symlink: <code>' . $path . '</code><br>';
        } elseif (is_dir($path)) {
            // Be careful — only remove if it looks like our mistake
            echo '<span class="warn">⚠️</span> Wrong directory exists: <code>' . $path . '</code> (remove manually if empty)<br>';
        }
    } else {
        echo '<span class="ok">✅</span> Already clean: <code>' . $path . '</code><br>';
    }
}

// --- 2. Create correct storage directories ---
echo '<h2>2. Creating storage directories in Laravel base</h2>';

$dirs = [
    $storagePublic,
    $storagePublic . '/uploads',
    $storagePublic . '/uploads/hr.document',
    $storagePublic . '/uploads/hr.employee_profile',
    $storagePublic . '/documents',
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo '<span class="ok">✅</span> Already exists: <code>' . str_replace($laravelBase, '', $dir) . '</code><br>';
    } elseif (mkdir($dir, 0755, true)) {
        echo '<span class="ok">✅</span> Created: <code>' . str_replace($laravelBase, '', $dir) . '</code><br>';
    } else {
        echo '<span class="fail">❌</span> Failed: <code>' . $dir . '</code><br>';
    }
}

// --- 3. Create symlink in document root ---
echo '<h2>3. Creating storage symlink in document root</h2>';

$docRootLink = $docRoot . '/storage';

if (is_link($docRootLink)) {
    $current = readlink($docRootLink);
    if ($current === $storagePublic) {
        echo '<span class="ok">✅</span> Symlink already correct: <code>storage → ' . $storagePublic . '</code><br>';
    } else {
        unlink($docRootLink);
        echo '<span class="warn">⚠️</span> Removed wrong symlink (was: <code>' . $current . '</code>)<br>';
        if (symlink($storagePublic, $docRootLink)) {
            echo '<span class="ok">✅</span> Created correct symlink: <code>storage → ' . $storagePublic . '</code><br>';
        } else {
            echo '<span class="fail">❌</span> symlink() failed. Manual command:<br>';
            echo '<pre>ln -s ' . $storagePublic . ' ' . $docRootLink . '</pre>';
        }
    }
} elseif (file_exists($docRootLink)) {
    echo '<span class="warn">⚠️</span> storage exists but is not a symlink. Remove it first.<br>';
} else {
    if (symlink($storagePublic, $docRootLink)) {
        echo '<span class="ok">✅</span> Created symlink: <code>storage → ' . $storagePublic . '</code><br>';
    } else {
        echo '<span class="fail">❌</span> symlink() failed. Manual command:<br>';
        echo '<pre>ln -s ' . $storagePublic . ' ' . $docRootLink . '</pre>';
    }
}

// --- 4. Verify ---
echo '<h2>4. Verification</h2>';

$checks = [
    'Laravel storage/app/public' => is_dir($storagePublic),
    'uploads/hr.document' => is_dir($storagePublic . '/uploads/hr.document'),
    'Document root storage symlink' => is_link($docRootLink) && readlink($docRootLink) === $storagePublic,
];

$allOk = true;
foreach ($checks as $label => $ok) {
    if ($ok) {
        echo '<span class="ok">✅</span> ' . $label . '<br>';
    } else {
        echo '<span class="fail">❌</span> ' . $label . '<br>';
        $allOk = false;
    }
}

if ($allOk) {
    echo '<p class="ok">✅ All good! Now upload your files to:</p>';
    echo '<pre>' . $storagePublic . '/uploads/</pre>';
} else {
    echo '<p class="warn">⚠️ Some items need attention.</p>';
}

echo '<hr><p><em>Delete this file after use.</em></p>';
echo '</body></html>';
