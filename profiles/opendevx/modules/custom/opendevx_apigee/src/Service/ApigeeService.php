<?php

namespace Drupal\opendevx_apigee\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Entity operation to rigel.
 */
class ApigeeService {

  use LoggerChannelTrait;

  /**
   * API url.
   *
   * @var string
   */
  const API_URL = 'https://api.enterprise.apigee.com/v1/organizations/';

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

  public $credentials;

  /**
   * Class constructor.
   */
  public function __construct(Client $client, ConfigFactoryInterface $configFactory) {
    $this->client = $client;
    $this->configFactory = $configFactory;
  }

  /**
   * Create Developer on Apigee.
   */
  public function createDeveloper($value) {
    $credentials = $value['credentials']['user_name'] . ':' . $value['credentials']['password'];
    $headers = [
      'Authorization' => 'Basic ' . base64_encode($credentials)  ,
      'Content-Type' => 'application/json',
    ];
      $endpoint = self::API_URL  . $value['credentials']['org_id'].'/developers/';
      try {
        $res = $this->client->request($value['method'], $endpoint, [
          'body' => json_encode($value['data']),
          'headers' => $headers,
          'timeout' => 5,
        ]);

        return json_decode($res->getBody()->getContents() ,TRUE);
      }
      catch (ClientException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
      catch (ConnectException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
     catch (BadResponseException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
  }


/**
   * Create Developer on Apigee.
   */
  public function getDeveloper($value) {
    $credentials = $value['credentials']['user_name'] . ':' . $value['credentials']['password'];
    $headers = [
      'Authorization' => 'Basic ' . base64_encode($credentials),
      'Content-Type' => 'application/json',
    ];
      $endpoint = self::API_URL  . $value['credentials']['org_id'].'/developers/';
      try {
        $res = $this->client->request('GET', $endpoint . '/' .$value['data']['email'] , [
          'headers' => $headers,
          'timeout' => 5,
        ]);

        return json_decode($res->getBody()->getContents(),TRUE);
      }
      catch (ClientException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
      catch (ConnectException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
      catch (BadResponseException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
  }


/**
   * Create app on Apigee.
   */
  public function createApp($value) {
    $credentials = $value['credentials']['user_name'] . ':' . $value['credentials']['password'];
    $headers = [
      'Authorization' => 'Basic ' . base64_encode($credentials),
      'Content-Type' => 'application/json',
    ];
    $endpoint = self::API_URL  . $value['credentials']['org_id'].'/developers/' . $value['credentials']['email'] . '/apps';
     try {
        $res = $this->client->request($value['method'], $endpoint, [
          'body' => json_encode($value['data']),
          'headers' => $headers,
          'timeout' => 5,
        ]);

        return json_decode($res->getBody()->getContents(),TRUE);
      }
      catch (ClientException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
      catch (ConnectException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
      catch (BadResponseException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
  }


/**
   * Get App from Apigee.
   */
  public function getApp($value) {
    $credentials = $value['credentials']['user_name'] . ':' . $value['credentials']['password'];
    $headers = [
      'Authorization' => 'Basic ' . base64_encode($credentials),
      'Content-Type' => 'application/json',
    ];
      $endpoint = self::API_URL  . $value['credentials']['org_id'].'/developers/' . $value['credentials']['email'] . '/apps';
      try {
        $res = $this->client->request('GET', $endpoint . '/' .$value['data']['name'] , [
          'headers' => $headers,
          'timeout' => 5,
        ]);
        return json_decode($res->getBody()->getContents(),TRUE);
      }
      catch (ClientException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
      catch (ConnectException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
      catch (BadResponseException $e) {
        $logger = $this->getLogger('opendevx-apigee-service');
        $logger->error($e->getMessage());
      }
     
  }

}
