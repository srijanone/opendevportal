<?php

namespace Drupal\odp_node;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Node class to handle node functionalities.
 */
class Node {

  use LoggerChannelTrait;

  /**
   * Node Id.
   *
   * @var int
   */
  protected $nodeId = NULL;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * User Account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    Connection $connection,
    AccountInterface $account,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->connection = $connection;
    $this->account = $account;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Set Node id.
   *
   * @param int $node_id
   *   Node Id.
   */
  public function setNodeId($node_id) {
    $this->nodeId = $node_id;
    return $this;
  }

  /**
   * Get Node Id.
   */
  public function getNodeId() {
    return $this->nodeId;
  }

  /**
   * Method to Check if node exist.
   */
  public function checkNodeExists() {
    try {
      $query = $this->connection->select('node_field_data', 'n');
      $query->fields('n', ['nid'])
        ->condition('n.nid', $this->nodeId, '=')
        ->range(0, 1);
      $result = $query->execute()->fetchAll();
      if (empty($result)) {
        return FALSE;
      }

      return TRUE;
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('developer-portal-node-exists');
      $logger->error($e->getMessage());
    }
  }

  /**
   * Get node title.
   */
  public function getTitle() {
    $result = '';
    try {
      $query = $this->connection->select('node_field_data', 'n');
      $query->addField('n', 'title');
      $query->condition('n.nid', $this->nodeId, '=');
      $result = $query->execute()->fetchField();
      if (!empty($result)) {
        return $result;
      }
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('developer-portal-node-title');
      $logger->error($e->getMessage());
    }
  }

  /**
   * Delete Api Reference.
   */
  public function deleteApiReference($entity_id) {
    $result = '';
    try {
      $query = $this->connection->select('node__field_api_specifications', 'nfas');
      $query->addField('nfas', 'entity_id');
      $query->condition('nfas.field_api_specifications_target_id', $entity_id, '=');
      $result = $query->execute()->fetchAll();

      $query = $this->connection->delete('node__field_api_specifications');
      $query->condition('field_api_specifications_target_id', $entity_id, '=');
      $query->execute();
      if (!empty($result)) {
        foreach ($result as $key => $value) {
          $node = $this->entityTypeManager->getStorage('node')->load($value->entity_id);
          $node->save();
        }
      }
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('developer-portal-api-reference');
      $logger->error($e->getMessage());
    }
  }

}
