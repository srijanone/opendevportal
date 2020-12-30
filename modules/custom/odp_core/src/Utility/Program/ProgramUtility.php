<?php

namespace Drupal\odp_core\Utility\Program;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to extend and provide the features of groups.
 */
class ProgramUtility {

  /**
   * Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * Fetching Group Type.
   *
   * @param int $group_id
   *   Group id.
   *
   * @return string
   *   Program type.
   */
  public function getProgramType($group_id) {
    $query = $this->connection->select('groups', 'g');
    $query->addField('g', 'type');
    $query->condition('g.id', $group_id);

    return $query->execute()->fetchField();
  }

  /**
   * Fetching Program Name.
   *
   * @param int $group_id
   *   Group id.
   *
   * @return string
   *   Program name.
   */
  public function getProgramName($group_id) {
    $query = $this->connection->select('groups_field_data', 'gdata');
    $query->addField('gdata', 'label');
    $query->condition('gdata.id', $group_id);

    return $query->execute()->fetchField();
  }

}
