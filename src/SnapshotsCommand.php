<?php

/**
 * @file
 * Contains PNX\Dashboard\SnapshotsCommand
 */

namespace PNX\Dashboard;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command for getting snapshots.
 */
class SnapshotsCommand extends Command {


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
    $this->setName('snapshots')
      ->setDescription("Query the PNX Dashboard API for snapshot data.")
      ->addOption('base-url', 'u', InputArgument::OPTIONAL, "The base url of the Dashboard API", "https://status.previousnext.com.au")
      ->addOption('alert-level', 'l', InputArgument::OPTIONAL, "Filter by the alert level.")
      ->addOption('client-id', 'c', InputArgument::OPTIONAL, "Filter by the client ID.")
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

    $client_id = $input->getOption('client-id');
    if (isset($client_id)) {
      $options['query']['client_id'] = $client_id;
    }

    $response = $this->client->get('snapshots', $options);

    if ($response->getStatusCode() != 200) {
      $output->writeln("Error calling dashboard API");
    }
    else {

      $json = $response->getBody();
      $sites = json_decode($json, TRUE);

      $table = new Table($output);
      $table
        ->setHeaders([
          'Timestamp',
          'Client ID',
          'Site ID',
          'Notice',
          'Warning',
          'Error'
        ]);

      foreach ($sites as $site) {
        $table->addRow([
          $site['timestamp'],
          $site['client_id'],
          $site['site_id'],
          $this->formatAlert('notice', $site['alert_summary']['notice']),
          $this->formatAlert('warning', $site['alert_summary']['warning']),
          $this->formatAlert('error', $site['alert_summary']['error']),
        ]);
      }

      $table->setStyle('borderless');
      $table->render();
    }

  }

  /**
   * Formats the alert level count.
   *
   * @param string $alert_level
   *   The alert level.
   * @param int $count
   *   The alert level count.
   *
   * @return string
   *   The formatted count.
   */
  protected function formatAlert($alert_level, $count) {
    if ($count > 0) {
      switch ($alert_level) {
        case 'error':
          return "<error>$count</error>";

        case 'warning':
          return "<comment>$count</comment>";
      }
    }
    return $count;
  }
}
