<?php

require_once('vendor/autoload.php');

use Alfred\Workflows\Workflow;

$workflow = new Workflow();
$keyDirectory = $workflow->env('KEY_DIR', '~/.ssh');

// Expand ignored file setting into an array
$ignore = array_map(
    'trim',
    explode(',', $workflow->env('IGNORE', '.,..,.DS_Store,authorized_keys,config,known_hosts'))
);

// Expand `~/` to full path
if (str_starts_with($keyDirectory, '~/')) {
    $fullHomePath = $workflow->env('HOME').'/';
    $keyDirectory = substr_replace(
        $keyDirectory,
        $fullHomePath,
        0,
        strlen('~/')
    );
}

if (! str_ends_with($keyDirectory, '/')) {
    $keyDirectory .= '/';
}

$files = scandir($keyDirectory);
$options = [];

foreach ($files as $file) {
    $fullPath = $keyDirectory . $file;

    if (is_dir($fullPath)) {
        // We’re only checking files
        continue;
    }

    if (in_array($file, $ignore, true)) {
        continue;
    }

    $content = file_get_contents($fullPath);

    if (str_starts_with($content, '----')) {
        // Most likely a private key, and we don’t want those
        continue;
    }

    $options[] = [
        'filename' => $file,
        'path' => $keyDirectory . $file,
    ];
}

$workflow->logger()->log($options);

foreach ($options as $option) {
    $workflow->item()
        ->title($option['filename'])
        ->subtitle($option['path'])
        ->arg($option['path']);
}

$workflow->output();
