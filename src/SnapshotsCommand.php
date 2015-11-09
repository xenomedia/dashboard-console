<?php

/**
 * @file
 * Contains PNX\Dashboard\SnapshotsCommand
 */

namespace Pnx\Dashboard;

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

    $client = new Client([
      'base_uri' => $input->getOption('base-url'),
      'auth' => [$input->getOption('username'), $input->getOption('password')],
      'headers' => [
        'Content-Type' => 'application/json'
      ]
    ]);

    $options = [
      'query' => []
    ];

    $alert_level = $input->getOption('alert-level');
    if (isset($alert_level)) {
      $options['query']['alert_level'] = $alert_level;
    }

    $client_id = $input->getOption('client-id');
    if (isset($client_id)) {
      $options['query']['client_id'] = $client_id;
    }

    $response = $client->get('snapshots', $options);

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
          $site['alert_summary']['notice'],
          $site['alert_summary']['warning'],
          $site['alert_summary']['error'],
        ]);
      }

      $table->setStyle('borderless');
      $table->render();
    }

  }

}
