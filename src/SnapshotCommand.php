<?php

/**
 * @file
 * Contains PNX\Dashboard\SnapshotCommand
 */

namespace PNX\Dashboard;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a command for querying a snapshot detail.
 */
class SnapshotCommand extends Command {

  /**
   * The maximum length of the description field.
   */
  const MAX_LENTH = 45;

  /**
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * SnapshotsCommand constructor.
   */
  public function __construct(Client $client) {
    parent::__construct();
    $this->client = $client;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('snapshot')
      ->setDescription("Query the PNX Dashboard API for snapshot data.")
      ->addOption('base-url', 'u', InputArgument::OPTIONAL, "The base url of the Dashboard API", "https://status.previousnext.com.au")
      ->addOption('alert-level', 'l', InputArgument::OPTIONAL, "Filter by the alert level.")
      ->addOption('site-id', 's', InputArgument::OPTIONAL, "The site ID.")
      ->addOption('username', NULL, InputArgument::OPTIONAL, "The Dashboard API username.", "admin")
      ->addOption('password', 'p', InputArgument::OPTIONAL, "The Dashboard API password.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $options = [
      'query' => [],
      'base_uri' => $input->getOption('base-url'),
      'auth' => [$input->getOption('username'), $input->getOption('password')],
    ];

    $alert_level = $input->getOption('alert-level');
    if (isset($alert_level)) {
      $options['query']['alert_level'] = $alert_level;
    }

    $site_id = $input->getOption('site-id');

    $response = $this->client->get('snapshots/' . $site_id, $options);

    if ($response->getStatusCode() != 200) {
      $output->writeln("Error calling dashboard API");
    }
    else {

      $json = $response->getBody();
      $snapshot = json_decode($json, TRUE);

      $table = new Table($output);
      $table->addRow(['Timestamp:', $snapshot['timestamp']]);
      $table->addRow(['Client ID:', $snapshot['client_id']]);
      $table->addRow(['Site ID:', $snapshot['site_id']]);

      $table->setStyle('compact');
      $table->render();

      $checks = $snapshot['checks'];

      $table = new Table($output);
      $table
        ->setHeaders([
          'Type',
          'Name',
          'Description',
          'Alert Level',
        ]);

      foreach ($checks as $check) {
        $table->addRow([
          $check['type'],
          $check['name'],
          $this->truncate($check['description']),
          $this->formatAlert($check['alert_level']),
        ]);
      }

      $table->setStyle('borderless');
      $table->render();
    }

  }

  /**
   * Truncate a string if it exceeds a the default length.
   *
   * @param string $value
   *   The value to truncate.
   *
   * @return string
   *   The truncated string.
   */
  protected function truncate($value) {
    return strlen($value) > self::MAX_LENTH ? substr($value, 0, self::MAX_LENTH) . "…" : $value;
  }

  /**
   * Apply output formatting for alerts.
   *
   * @param string $alert
   *   The alert.
   *
   * @return string
   *   The formatted output for the alert.
   */
  protected function formatAlert($alert) {
    switch ($alert) {
      case 'error':
        return "<error>$alert</error>";

      case 'warning':
        return "<comment>$alert</comment>";

      default:
        return "<info>$alert</info>";
    }
  }
}
