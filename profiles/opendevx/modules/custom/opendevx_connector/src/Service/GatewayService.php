<?php

namespace Drupal\opendevx_connector\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;

/**
 * Entity operation to rigel.
 */
class GatewayService {

  use LoggerChannelTrait;

  /**
   * API url.
   *
   * @var string
   */
  const API_URL = '/jsonapi/node/';

  /**
   * Http client.
   *
   * @var object
   */
  public $client;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Class constructor.
   */
  public function __construct(Client $client, ConfigFactoryInterface $configFactory) {
    $this->client = $client;
    $this->configFactory = $configFactory;
  }

  /**
   * Send Data to HUB.
   */
  public function sendEntity($value) {
    $endpoint = '';
    $config = $this->configFactory->get('opendevx_connector.settings');
    $headers = [
      'Authorization' => 'Basic ' . $config->get('cred'),
      'Content-Type' => 'application/vnd.api+json',
    ];
    if ($config->get('url')) {
      switch ($value['method']) {
        case 'PATCH':
          $endpoint = self::API_URL . $value['entity'] . '/' . $value['value']['data']['id'];
          break;

        case 'POST':
          $endpoint = self::API_URL . $value['entity'];
          $value['value']['data']['relationships']['field_portalid']['data'] = [
            'type' => 'node--portal',
            'id' => $config->get('portal_id'),
          ];
          break;

        case 'DELETE':
          $endpoint = self::API_URL . $value['entity'] . '/' . $value['value']['data']['id'];
          break;
      }
      try {
        $res = $this->client->request($value['method'], $config->get('url') . $endpoint, [
          'body' => json_encode($value['value']),
          'headers' => $headers,
          'timeout' => 5,
        ]);

        return $res->getBody()->getContents();
      }
      catch (ClientException $e) {
        $logger = $this->getLogger('opendevx_connector-service');
        $logger->error($e->getMessage());
      }
      catch (ConnectException $e) {
        $logger = $this->getLogger('opendevx_connector-service');
        $logger->error($e->getMessage());
      }
    }
  }

}
