<?php

require_once('vendor/autoload.php');

use Alfred\Workflows\Workflow;
use MattStein\CopySsh\SshKeyScanner;

$workflow = new Workflow();

$keyDirectory = SshKeyScanner::expandPath(
    $workflow->env('KEY_DIR', '~/.ssh'),
    $workflow->env('HOME')
);

$ignore = SshKeyScanner::parseIgnoreList(
    $workflow->env('IGNORE', '.,..,.DS_Store,authorized_keys,config,known_hosts')
);

$options = SshKeyScanner::scan($keyDirectory, $ignore);

$workflow->logger()->log($options);

foreach ($options as $option) {
    $workflow->item()
        ->title($option['filename'])
        ->subtitle($option['path'])
        ->arg($option['path']);
}

$workflow->output();
