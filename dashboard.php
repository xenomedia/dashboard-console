#!/usr/bin/env php
<?php
/**
 * @file
 * Console application for PNX Dashboard.
 */

require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use PNX\Dashboard\SnapshotCommand;
use PNX\Dashboard\SnapshotsCommand;
use Symfony\Component\Console\Application;


$client = new Client([
  'headers' => [
    'Content-Type' => 'application/json'
  ]
]);

$application = new Application();
$application->add(new SnapshotsCommand($client));
$application->add(new SnapshotCommand($client));
$application->run();
