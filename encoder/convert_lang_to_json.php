<?php

use Illuminate\Filesystem\Filesystem;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$undo = in_array('--undo', $argv);

// Explicit paths - only lang files
$paths = [
    app()->langPath(),
    base_path('vendor/laravel/framework/src/Illuminate/Translation/lang'),
];

function flatten($array, $prefix = '') {
    $result = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $result = array_merge($result, flatten($value, $prefix . $key . '.'));
        } else {
            $result[$prefix . $key] = $value;
        }
    }
    return $result;
}

function processLocaleDirectory($basePath, $locale, $undo) {
    $localePath = $basePath . '/' . $locale;
    $jsonPath = $basePath . '/' . $locale . '.json';

    if (!is_dir($localePath)) {
        return;
    }

    if ($undo) {
        // Undo: Delete json, rename .phpx -> .php
        if (file_exists($jsonPath)) {
            echo "Deleting: $jsonPath\n";
            unlink($jsonPath);
        }

        $iterator = new DirectoryIterator($localePath);
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'phpx') {
                $phpxPath = $file->getPathname();
                $phpPath = preg_replace('/\.phpx$/', '.php', $phpxPath);
                echo "Restoring: $phpPath\n";
                rename($phpxPath, $phpPath);
            }
        }
    } else {
        // Convert: Read .php, merge, write json, rename .php -> .phpx
        $translations = [];
        $filesProcessed = false;

        $iterator = new DirectoryIterator($localePath);
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filesProcessed = true;
                $filename = $file->getBasename('.php');
                echo "Processing: " . $file->getPathname() . "\n";

                try {
                    $data = include $file->getPathname();
                    if (is_array($data)) {
                        // Prefix keys with filename
                        $flat = flatten($data, $filename . '.');
                        $translations = array_merge($translations, $flat);
                    }
                } catch (Exception $e) {
                    echo "Error reading " . $file->getPathname() . ": " . $e->getMessage() . "\n";
                }
            }
        }

        if ($filesProcessed) {
            // Write JSON
            // Check if JSON already exists (maybe from previous run or manual), merge if so?
            // For now, assuming we are creating it fresh or overwriting as per "convert" logic.
            // But if we overwrite, we might lose manual entries if the user had a hybrid setup.
            // Given the prompt, we are "changing configuration ... to json", implying a migration.

            $jsonContent = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (file_put_contents($jsonPath, $jsonContent) !== false) {
                echo "Created: $jsonPath\n";

                // Now rename .php files
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'php') {
                        $phpxPath = preg_replace('/\.php$/', '.phpx', $file->getPathname());
                        rename($file->getPathname(), $phpxPath);
                    }
                }
            }
        }
    }
}

foreach ($paths as $baseDir) {
    if (!is_dir($baseDir)) {
        echo "Directory not found: $baseDir\n";
        continue;
    }

    echo "Scanning $baseDir...\n";

    // Find locale directories
    $iterator = new DirectoryIterator($baseDir);
    foreach ($iterator as $file) {
        if ($file->isDir() && !$file->isDot()) {
            $locale = $file->getFilename();
            processLocaleDirectory($baseDir, $locale, $undo);
        }
    }
}

echo "Done.\n";
