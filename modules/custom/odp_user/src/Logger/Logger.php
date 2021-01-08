<?php

namespace Drupal\odp_user\Logger;

use Drupal\Core\Logger\LoggerChannelTrait;

/**
 * Class Logger.
 *
 * @package Drupal\odp_user\Logger
 */
class Logger {

  use LoggerChannelTrait;

  /**
   * Opendevx Log function.
   *
   * @param array $log
   *   Array of logs. Allowed key in log are: module and message.
   */
  public function log(array $log) {
    $logger = $this->getLogger($log['module']);
    $logger->error($log['message']);
  }

}
