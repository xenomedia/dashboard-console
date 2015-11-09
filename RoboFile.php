<?php
/**
 * @file
 * Contains \Robo\RoboFile.
 *
 * Robo build file - http://robo.li/
 */

use Robo\Tasks;

/**
 * Class RoboFile.
 */
class RoboFile extends Tasks {

  /**
   * Initialize config variables and apply overrides.
   */
  public function __construct() {
    $this->src_dir = "src";
    $this->tests_dir = "tests";
    $this->phpcs_bin = "bin/phpcs";
    $this->phpunit_bin = "bin/phpunit";
  }

  /**
   * Run PHP Code Sniffer.
   */
  public function phpcs() {
    $this->_exec("$this->phpcs_bin --config-set installed_paths vendor/drupal/coder/coder_sniffer");
    return $this->_exec("$this->phpcs_bin --standard=Drupal $this->src_dir");
  }

  /**
   * Run tests.
   */
  public function test() {
    return $this->_exec("$this->phpunit_bin");
  }
}
