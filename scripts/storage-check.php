<?php

/**
 * Storage Diagnostic Script
 * -------------------------
 * Upload this file to the server's public/ directory and access it via:
 *   https://agriwatts.quickerfaster.com/storage-check.php
 *
 * Delete this file after use — it exposes server paths.
 */

echo '<!DOCTYPE html><html><head><title>Storage Diagnostic</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0}h2{color:#00d4ff;border-bottom:1px solid #333;padding-bottom:5px}.ok{color:#00ff88}.fail{color:#ff4466}.warn{color:#ffaa00}pre{background:#0d0d1a;padding:10px;border-radius:4px;overflow-x:auto}</style>';
echo '</head><body><h1>🔍 Storage Diagnostic</h1>';

$base = dirname(__DIR__); // project root (one level above public/)

echo '<h2>1. Project root</h2>';
echo '<pre>' . $base . '</pre>';

// --- Check public/storage ---
echo '<h2>2. public/storage</h2>';
$publicStorage = $base . '/public/storage';
if (file_exists($publicStorage)) {
    if (is_link($publicStorage)) {
        $target = readlink($publicStorage);
        echo '<span class="ok">✅ Symlink exists</span><br>';
        echo 'Target: <code>' . $target . '</code><br>';
        if (file_exists($publicStorage . '/')) {
            echo '<span class="ok">✅ Target is accessible</span>';
        } else {
            echo '<span class="fail">❌ Target is BROKEN (destination does not exist)</span>';
        }
    } else {
        echo '<span class="warn">⚠️  Exists but is NOT a symlink (it is a ' . (is_dir($publicStorage) ? 'directory' : 'file') . ')</span>';
    }
} else {
    echo '<span class="fail">❌ public/storage does NOT exist</span>';
}

// --- Check storage/app/public ---
echo '<h2>3. storage/app/public</h2>';
$storagePublic = $base . '/storage/app/public';
if (is_dir($storagePublic)) {
    echo '<span class="ok">✅ Directory exists</span>';
} else {
    echo '<span class="fail">❌ Directory does NOT exist</span>';
}

// --- Check uploads/hr.document ---
echo '<h2>4. storage/app/public/uploads/hr.document/</h2>';
$uploadsDir = $storagePublic . '/uploads/hr.document';
if (is_dir($uploadsDir)) {
    $files = scandir($uploadsDir);
    $files = array_diff($files, ['.', '..']);
    $count = count($files);
    echo '<span class="ok">✅ Directory exists — ' . $count . ' file(s)</span>';
    if ($count > 0) {
        echo '<pre>';
        foreach ($files as $f) {
            $size = filesize($uploadsDir . '/' . $f);
            echo sprintf("%-60s %s\n", $f, humanSize($size));
        }
        echo '</pre>';
    }
} else {
    echo '<span class="fail">❌ Directory does NOT exist</span>';
}

// --- Check documents/ ---
echo '<h2>5. storage/app/public/documents/</h2>';
$docsDir = $storagePublic . '/documents';
if (is_dir($docsDir)) {
    $subdirs = scandir($docsDir);
    $subdirs = array_diff($subdirs, ['.', '..']);
    echo '<span class="ok">✅ Directory exists — ' . count($subdirs) . ' subfolder(s)</span>';
    echo '<pre>';
    foreach ($subdirs as $d) {
        if (is_dir($docsDir . '/' . $d)) {
            $inner = array_diff(scandir($docsDir . '/' . $d), ['.', '..']);
            echo sprintf("%-30s %d file(s)\n", $d . '/', count($inner));
        }
    }
    echo '</pre>';
} else {
    echo '<span class="fail">❌ Directory does NOT exist</span>';
}

// --- Check specific file from the link ---
echo '<h2>6. Specific file from the broken link</h2>';
$testFile = $uploadsDir . '/j7mq4VEsCUEMVQavPIrAO8UqvbZeKJlLFJ1ley1K.pdf';
echo 'Path: <code>' . $testFile . '</code><br>';
if (file_exists($testFile)) {
    echo '<span class="ok">✅ File EXISTS (' . humanSize(filesize($testFile)) . ')</span>';
} else {
    echo '<span class="fail">❌ File does NOT exist</span>';
}

// --- Check Laravel filesystems config ---
echo '<h2>7. Filesystem disks (from Laravel config)</h2>';
echo '<pre>';
if (function_exists('config')) {
    $disks = config('filesystems.disks', []);
    foreach (['public', 'documents', 'local'] as $name) {
        if (isset($disks[$name])) {
            echo sprintf("%-15s root: %s\n", $name . ':', $disks[$name]['root'] ?? 'N/A');
            echo sprintf("%-15s url:  %s\n", '', $disks[$name]['url'] ?? 'N/A');
        } else {
            echo sprintf("%-15s NOT CONFIGURED\n", $name . ':');
        }
    }
} else {
    echo 'Laravel not booted — cannot read config.';
}
echo '</pre>';

// --- Check if storage:link was run ---
echo '<h2>8. Quick fix if symlink is missing</h2>';
echo '<p>Run this on the server (SSH) or add to .cpanel.yml:</p>';
echo '<pre>php artisan storage:link</pre>';
echo '<p>Or create the symlink manually:</p>';
echo '<pre>ln -s ' . $storagePublic . ' ' . $publicStorage . '</pre>';

echo '<hr><p><em>Delete this file after use.</em></p>';
echo '</body></html>';

function humanSize($bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 1) . ' ' . $units[$i];
}
