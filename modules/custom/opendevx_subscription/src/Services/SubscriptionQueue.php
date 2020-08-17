<?php

namespace Drupal\opendevx_subscription\Services;

use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\opendevx_subscription\SubscriptionQueueInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\opendevx_subscription\Form\SubscriptionSettings;
use Drupal\node\NodeInterface;

/**
 * Service handler for Subscription Service.
 */
class SubscriptionQueue implements SubscriptionQueueInterface {

  const SUBSCRIPTION_QUEUED = 0;
  const SUBSCRIPTION_SENT = 1;
  const SUBSCRIPTION_FAILURE = 2;
  const TABLE_NAME = 'opendevx_subscription';

  /**
   * Symfony\Component\HttpFoundation\Request definition.
   *
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection,
  RequestStack $request,
  ConfigFactoryInterface $config_factory,
  LoggerChannelFactory $loggerFactory,
  TimeInterface $time) {
    $this->database      = $connection;
    $this->request       = $request;
    $this->configFactory = $config_factory->getEditable(SubscriptionSettings::MODULE_KEY . '.settings');
    $this->loggerFactory = $loggerFactory->get('activity_tracking');
    $this->time          = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setQueueRecords(EntityInterface $entity) {
    if (!$entity instanceof NodeInterface) {
      return FALSE;
    }
    $fields = $this->prepareQueueData($entity, 'update');
    if (empty($fields)) {
      return FALSE;
    }

    $keys = [
      'id' => NULL,
    ];
    foreach ($fields as $data) {
      try {
        $this->database->merge(self::TABLE_NAME)
          ->key($keys)
          ->fields($data)
          ->execute();
      }
      catch (Exception $e) {
        // Exception handling if something else gets thrown.
        $this->loggerFactory->error($e->getMessage());
      }
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubsribedUsers(int $nid) {
    if (!$nid) {
      $nid = \Drupal::routeMatch()->getParameter('node');
      if (!$nid) {
        return FALSE;
      }
    }
    try {
      $query = $this->database->select('user__field_subscribed_products', 'sp');
      $query->fields('sp', ['entity_id']);
      $query->condition('field_subscribed_products_target_id', $nid, '=');
      $result = $query->execute()->fetchCol();
      if (empty($result)) {
        return FALSE;
      }

      return $result;
    }
    catch (\Exception $e) {
      $this->loggerFactory->error($e->getMessage());
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscribedContent($uid) {

  }

  /**
   * {@inheritdoc}
   */
  public function getQueueRecords(int $limit, int $offset) {
    try {
      $query = $this->database->select(self::TABLE_NAME, 'n');
      $query->fields('n', [
        'id', 'entity_id', 'bundle', 'nid',
        'subscriber', 'action', 'status', 'created',
      ]);

      $res = $query->range($offset, $limit)->execute()->fetchAll();
    }
    catch (\Exception $e) {
      $this->loggerFactory->error($e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateQueueRecord(int $id) {
    $this->database->update(self::TABLE_NAME)
      ->condition('id', $id)
      ->fields(['status' => self::SUBSCRIPTION_SENT])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteQueueRecord(int $id) {
    try {
      // Below query deletes all instances where entity_id meets the condition.
      $query = $this->database->delete(self::TABLE_NAME);
      $query->condition('entity_id', $id);
      $query->execute();
    }
    catch (\Exception $e) {
      // Exception handling if something else gets thrown.
      $this->loggerFactory->error($e->getMessage());
    }
  }

  /**
   * Helper function to prepare data for queue.
   */
  protected function prepareQueueData(EntityInterface $entity, $action) {
    // Store current entity ID in variable.
    $entity_id = $entity->id();
    $bundle = $entity->bundle();
    $add_to_queue = FALSE;

    // Check if bundle is selected in the subscription settings.
    if (!isset($this->configFactory->get('entity_types')[$bundle])) {
      return FALSE;
    }

    // Check if entity has api_product referenced.
    $field_name = 'field_api_product';
    if ($entity->hasField($field_name)) {
      // Check if this entity has the product assigned.
      $ref_products = array_column($entity->get($field_name)->getValue(),
      'target_id');
      if (!empty($ref_products)) {
        $add_to_queue = TRUE;
      }
    }
    elseif ($bundle == 'api_product') {
      $add_to_queue = TRUE;
      $ref_products = [$entity_id];
    }
    if (!$add_to_queue) {
      return FALSE;
    }
    $data = [];
    foreach ($ref_products as $product_id) {
      $product_id = (int) $product_id;
      // Get users who subscribed to the particular product.
      $users = $this->getSubsribedUsers($product_id);
      if ($users) {
        $data[$product_id] = [
          'entity_id'   => (int) $entity_id,
          'bundle'      => $bundle,
          'nid'         => $product_id,
          'subscribers' => serialize($users),
          'action'      => $action,
          'status'      => self::SUBSCRIPTION_QUEUED,
          'created'     => $this->time->getRequestTime(),
        ];
      }
    }

    return $data;
  }

}
