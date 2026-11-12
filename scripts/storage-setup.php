<?php

/**
 * Storage Setup Script
 * --------------------
 * Creates the missing storage/ directory structure and the public/storage
 * symlink on the server. Run once, then delete.
 *
 * Upload to public/ and access via:
 *   https://agriwatts.quickerfaster.com/storage-setup.php
 */

set_time_limit(30);

$base = dirname(__DIR__); // project root
$errors = [];
$created = [];

echo '<!DOCTYPE html><html><head><title>Storage Setup</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0}h2{color:#00d4ff}.ok{color:#00ff88}.fail{color:#ff4466}.warn{color:#ffaa00}pre{background:#0d0d1a;padding:10px;border-radius:4px}</style>';
echo '</head><body><h1>🛠 Storage Setup</h1>';
echo '<p>Project root: <code>' . $base . '</code></p>';

// --- 1. Create storage directory structure ---
echo '<h2>1. Creating storage directories</h2>';

$dirs = [
    '/storage',
    '/storage/app',
    '/storage/app/public',
    '/storage/app/public/uploads',
    '/storage/app/public/uploads/hr.document',
    '/storage/app/public/uploads/hr.employee_profile',
    '/storage/app/public/documents',
    '/storage/framework',
    '/storage/framework/cache',
    '/storage/framework/cache/data',
    '/storage/framework/sessions',
    '/storage/framework/views',
    '/storage/logs',
];

foreach ($dirs as $dir) {
    $path = $base . $dir;
    if (is_dir($path)) {
        echo '<span class="ok">✅</span> Already exists: <code>' . $dir . '</code><br>';
    } elseif (mkdir($path, 0755, true)) {
        echo '<span class="ok">✅</span> Created: <code>' . $dir . '</code><br>';
        $created[] = $dir;
    } else {
        echo '<span class="fail">❌</span> Failed: <code>' . $dir . '</code><br>';
        $errors[] = $dir;
    }
}

// --- 2. Create .gitignore in storage/app/public ---
$gitignore = $base . '/storage/app/public/.gitignore';
if (!file_exists($gitignore)) {
    file_put_contents($gitignore, "*\n!.gitignore\n");
    echo '<span class="ok">✅</span> Created .gitignore in storage/app/public/<br>';
}

// --- 3. Create public/storage symlink ---
echo '<h2>2. Creating public/storage symlink</h2>';

$link = $base . '/public/storage';
$target = $base . '/storage/app/public';

if (file_exists($link)) {
    echo '<span class="warn">⚠️</span> public/storage already exists<br>';
} elseif (symlink($target, $link)) {
    echo '<span class="ok">✅</span> Symlink created: <code>public/storage → storage/app/public</code><br>';
} else {
    $symlinkError = error_get_last();
    echo '<span class="fail">❌</span> Could not create symlink.<br>';
    echo '<p>PHP symlink() failed. This is common on shared hosting.</p>';
    echo '<p><strong>Manual fix required:</strong> The hosting admin must run:</p>';
    echo '<pre>ln -s ' . $target . ' ' . $link . '</pre>';
    echo '<p>Or add this to <code>.cpanel.yml</code> deploy script:</p>';
    echo '<pre>deploy: |
  ln -sf ' . $target . ' ' . $link . '</pre>';
    $errors[] = 'public/storage symlink';
}

// --- 4. Verify ---
echo '<h2>3. Verification</h2>';

$checks = [
    'storage/app/public' => is_dir($base . '/storage/app/public'),
    'storage/app/public/uploads/hr.document' => is_dir($base . '/storage/app/public/uploads/hr.document'),
    'storage/framework/cache/data' => is_dir($base . '/storage/framework/cache/data'),
    'storage/framework/sessions' => is_dir($base . '/storage/framework/sessions'),
    'storage/framework/views' => is_dir($base . '/storage/framework/views'),
    'storage/logs' => is_dir($base . '/storage/logs'),
    'public/storage (symlink)' => is_link($base . '/public/storage'),
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

// --- 5. Summary ---
echo '<h2>4. Summary</h2>';

if (empty($errors) && $allOk) {
    echo '<p class="ok">✅ All good! The storage structure is ready.</p>';
    echo '<p><strong>Next step:</strong> Upload your local files to the server:</p>';
    echo '<pre>scp -r storage/app/public/uploads/* user@server:' . $base . '/storage/app/public/uploads/</pre>';
    echo '<p>Or re-upload documents through the application.</p>';
} else {
    echo '<p class="warn">⚠️ Some items need attention (see above).</p>';
    if (!empty($errors)) {
        echo '<p>Failed items:</p><pre>';
        foreach ($errors as $e) echo $e . "\n";
        echo '</pre>';
    }
}

echo '<hr><p><em>Delete this file after use.</em></p>';
echo '</body></html>';
