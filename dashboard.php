#!/usr/bin/env php
<?php
/**
 * @file
 * Console application for PNX Dashboard.
 */

require __DIR__ . '/vendor/autoload.php';

use PNX\Dashboard\SnapshotsCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new SnapshotsCommand());
$application->run();

