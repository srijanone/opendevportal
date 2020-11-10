<?php

namespace Drupal\opendevx_apigee\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use Drupal\node\Entity\Node;
use Drupal\group\Entity\Group;
use Drupal\Core\Database\Connection;

/**
 * Provides controller class SyncApiDataController.
 */
class SyncApiDataController extends ControllerBase {

  const URL = 'https://api.enterprise.apigee.com';
  const ENDPOINT = '/v1/organizations/';

  /**
   * Http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Email.
   *
   * @var string
   */
  protected $email;

  /**
   * Password.
   *
   * @var string
   */
  protected $password;

  /**
   * Organisation.
   *
   * @var string
   */
  protected $org;

  /**
   * SyncApiDataController constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   Http client.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(ClientInterface $httpClient, Connection $connection) {
    $this->httpClient = $httpClient;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('database')
    );
  }

  /**
   * Process batch Api.
   */
  public function processApiData() {
    $pid = \Drupal::request()->query->get('pid');
    $group = Group::load($pid);
    $gateway = $group->field_gateway->entity->field_gateway->entity->name->value;
    if (!in_array(strtolower($gateway), ['apigee enterprise', 'apigee hybrid'])) {
      return drupal_set_message('Please select the "Apigee" as Gateway.');
    }
    // Get available proxies.
    $this->email = $group->field_gateway->entity->field_client_id->value;
    $this->password = $group->field_gateway->entity->field_client_secret->value;
    $this->org = $group->field_gateway->entity->field_region->value;
    $tagged_proxies = $this->getTaggedProxies($pid);
    $proxies = $this->getApiProxies();
    $proxy_data = [
      'group' => $group,
      'email' => $this->email,
      'password' => $this->password,
      'tagged_proxy' => $tagged_proxies,
      'org' => $this->org,
    ];
    foreach ($proxies as $key => $proxy) {
      $proxy_data['proxy'] = $proxy;
      $operations[] = ['Drupal\opendevx_apigee\Controller\SyncApiDataController::batchProcess',
        [
          $proxy_data, $this->t('(Operation @operation)', ['@operation' => $key]),
        ],
      ];
    }
    $batch = [
      'title' => $this->t("Synchronizing Entities."),
      'operations' => $operations,
      'finished' => 'Drupal\opendevx_apigee\Controller\SyncApiDataController::batchFinished',
    ];
    try {
      batch_set($batch);
    }
    catch (\Exception $e) {
    }

    return batch_process('dashboard/manage/program');
  }

  /**
   * Common batch processing callback for all operations.
   */
  public static function batchProcess($data, $operation_details, &$context) {
    $context['message'] = t('Synchronizing Entities');
    $revision = self::getProxyRevisionIds($data);
    $revision_proxy = self::getProxyRevision($data, $revision);
    $node = '';
    if (isset($data['tagged_proxy'][$revision_proxy['name']])) {
      $node = Node::load($data['tagged_proxy'][$revision_proxy['name']]);
      $node->set('changed', time());
      $node->save();
    }
    else {
      $revision_spec = self::getProxyExport($data, $revision);
      $pub_file_path = 'public://' . date('Y') . '-' . date('m');
      if (file_prepare_directory($pub_file_path, FILE_CREATE_DIRECTORY)) {
        $api_file = file_save_data($revision_spec, $pub_file_path . '/' . $data['proxy'] . '-' . $revision . '.json', FILE_EXISTS_RENAME);
      }
      $node = Node::create([
        'type'        => 'api_document',
        'title'       => $revision_proxy['displayName'],
        'field_document' => [
          'target_id' => $api_file->id(),
        ],
        'field_apigee_proxy' => $revision_proxy['name'],
        'body' => [
          'value' => $revision_proxy['description'],
          'summary' => $revision_proxy['displayName'],
          'format' => 'full_html',
        ],
      ]);
      $node->save();
      $group = $data['group'];
      $group->addContent($node, 'group_node:api_document');
    }
    $context['results'][] = $node->id();
    $context['message'] = t('Api proxy "@title" processed.', [
      '@title' => $node->label(),
    ]);
  }

  /**
   * Batch finished callback.
   */
  public static function batchFinished($success, $results, $operations) {
    $messenger = \Drupal::messenger();
    if ($success) {
      $messenger->addMessage(t('Total @count results processed.', ['@count' => count($results)]));
    }
    else {
      $error_operation = reset($operations);
      $messenger->addMessage(
        t('An error occurred while processing @operation with arguments : @args',
          [
            '@operation' => $error_operation[0],
            '@args' => print_r($error_operation[0], TRUE),
          ]
        )
      );
    }
  }

  /**
   * Get Api Proxies.
   *
   * @return array
   *   Lis of api proxies.
   */
  protected function getApiProxies() {
    try {
      $request = $this->httpClient->get(
        self::URL . self::ENDPOINT . $this->org . '/apis', [
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($this->email . ':' . $this->password),
          ],
        ]
      );
      return json_decode($request->getBody()->getContents(), TRUE);
    }
    catch (RequestException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * Get Latest Proxy revision.
   *
   * @return int
   *   Revision id.
   */
  protected static function getProxyRevisionIds($proxy) {
    try {
      $httpClient = new Client();
      $request = $httpClient->get(
        self::URL . self::ENDPOINT . $proxy['org'] . '/apis/' . $proxy['proxy'] . '/revisions', [
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($proxy['email'] . ':' . $proxy['password']),
          ],
        ]
      );
      $rid = json_decode($request->getBody()->getContents(), TRUE);
      return end($rid);
    }
    catch (RequestException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * Get Proxy revision detail.
   *
   * @return array
   *   Revision Detail.
   */
  protected static function getProxyRevision($proxy, $rev_id) {
    try {
      $httpClient = new Client();
      $request = $httpClient->get(
        self::URL . self::ENDPOINT . $proxy['org'] . '/apis/' . $proxy['proxy'] . '/revisions/' . $rev_id, [
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($proxy['email'] . ':' . $proxy['password']),
            'Content-Type' => 'application/json',
          ],
        ]
      );
      return json_decode($request->getBody()->getContents(), TRUE);
    }
    catch (RequestException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * Get Proxy revision detail.
   *
   * @return array
   *   Revision Detail.
   */
  protected static function getProxyExport($proxy, $rev_id) {
    try {
      $httpClient = new Client();
      $request = $httpClient->get(
        self::URL . self::ENDPOINT . $proxy['org'] . '/apis/' . $proxy['proxy'] . '/revisions/' . $rev_id, [
          'query' => [
            'format' => 'bundle',
          ],
          'headers' => [
            'Authorization' => 'Basic ' . base64_encode($proxy['email'] . ':' . $proxy['password']),
          ],
        ]
      );
      $file = $request->getBody()->getContents();
      $path = 'public://apiProxies/';
      $dest = $path . $proxy['proxy'] . '-' . $rev_id . '.zip';
      if (file_prepare_directory($path, FILE_CREATE_DIRECTORY)) {
        $zip_file = file_save_data($file, $dest, FILE_EXISTS_RENAME);
      }
      $archiver = archiver_get_archiver($zip_file->uri->value);
      if (!$archiver) {
        throw new Exception(t('Cannot extract %file, not a valid archive.', ['%file' => $my_file_obj->uri]));
      }
      $extract_location = $path . $proxy['proxy'] . '-' . $rev_id;
      $extractedFile = $archiver->extract($extract_location);
      $api_spec = self::getApiSpec($extract_location, $extractedFile);

      return $api_spec;
    }
    catch (RequestException $e) {
      echo $e->getMessage();
    }
  }

  /**
   * Get Tagged proxies.
   */
  protected function getTaggedProxies($pid) {
    $query = $this->connection->select('group_content_field_data', 'gcfd');
    $query->innerJoin('node__field_apigee_proxy', 'nfap', 'nfap.entity_id = gcfd.entity_id');
    $query->fields('nfap', ['field_apigee_proxy_value', 'entity_id'])
      ->condition('gcfd.gid', $pid)
      ->condition('nfap.deleted', 0);
    $result = $query->execute()->fetchAllKeyed(0, 1);

    return $result;
  }

  /**
   * Get Open API spec from extracted zip.
   */
  protected static function getApiSpec($path, $file) {
    return '{
      "openapi" : "3.0.1",
      "info" : {
        "title" : "API test",
        "description" : "my first API test",
        "version" : "2020-08-28T06:19:04Z"
      },
      "servers" : [ {
        "url" : "https://js4crdrbp8.execute-api.us-east-2.amazonaws.com/{basePath}",
        "variables" : {
          "basePath" : {
            "default" : "/v1"
          }
        }
      } ],
      "paths" : {
        "/" : {
          "get" : {
            "responses" : {
              "200" : {
                "description" : "200 response",
                "content" : {
                  "application/json" : {
                    "schema" : {
                      "$ref" : "#/components/schemas/Empty"
                    }
                  }
                }
              }
            }
          }
        }
      },
      "components" : {
        "schemas" : {
          "Empty" : {
            "title" : "Empty Schema",
            "type" : "object"
          }
        }
      }
    }';
  }

}
