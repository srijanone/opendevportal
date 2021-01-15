<?php

namespace Drupal\odp_api_documents;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Node class to handle node functionalities.
 */
class Node {

  use LoggerChannelTrait;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
    EntityTypeManagerInterface $entity_type_manager) {
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
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
        foreach ($result as $value) {
          $node = $this->entityTypeManager->getStorage('node')->load($value->entity_id);
          $node->save();
        }
      }
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('odp-api-documents-reference');
      $logger->error($e->getMessage());
    }
  }

}
