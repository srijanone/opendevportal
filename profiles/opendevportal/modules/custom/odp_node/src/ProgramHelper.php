<?php

namespace Drupal\odp_node;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Program Helper to get program and program related gateways.
 */
class ProgramHelper {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entity;

  /**
   * Constructs a new ProgramHelper object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity
   *   Entity type.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entity) {
    $this->database = $connection;
    $this->entity = $entity->getStorage('group');
  }

  /**
   * Get Program by node.
   */
  public function getProgramByNid($nid) {
    $query = $this->database->select('group_content_field_data', 'gcfd')
      ->fields('gcfd', ['gid']);
    $query->innerJoin('group_content', 'gc', 'gc.type = gcfd.type AND gc.id = gcfd.id');
    $query->condition('gcfd.entity_id', $nid, '=');
    $result = $query->execute()->fetchCol();

    return ($result) ? $result : [];
  }

  /**
   * Get Gateway of Programs.
   */
  public function getRelatedGateway($pid) {
    $gateways = [];
    if (is_array($pid)) {
      $programs = $this->entity->loadMultiple($pid);
      foreach ($programs as $id => $program) {
        $gateway = $program->field_gateway->referencedEntities();
        foreach ($gateway as $value) {
          $gateways[$value->id()] = $value->label();
        }
      }
    }
    else {
      $program = $this->entity->load($pid);
      $gateway = $program->field_gateway->referencedEntities();
      foreach ($gateway as $value) {
        $gateways[$value->id()] = $value->label();
      }
    }

    return $gateways;
  }

}
