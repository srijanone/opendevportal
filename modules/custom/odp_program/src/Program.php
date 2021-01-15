<?php

namespace Drupal\odp_program;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Views;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\odp_program\Utility\ProgramUtility;

/**
 * Class Program.
 *
 * @package Drupal\odp_program
 */
class Program {

  use LoggerChannelTrait;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Account Interface.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entity;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    Connection $connection,
    AccountInterface $account,
    EntityTypeManagerInterface $entity) {
    $this->account = $account;
    $this->connection = $connection;
    $this->entity = $entity->getStorage('group');
  }

  /**
   * Function to get program information.
   *
   * @return array
   *   Program data.
   */
  public function getProgramData() {
    $organisation = [];
    try {
      $view = Views::getView("programs");
      $view->setDisplay("featured_program");
      $view->execute();
      if ($view) {
        foreach ($view->result as $key => $value) {
          $gid = $value->_entity->get('id')->value;
          $organisation[$gid]['orgId'] = $gid;
          $organisation[$gid]['orgName'] = $value->_entity->get('label')->value;
          if ($image = $value->_entity->get('field_program_image')->getValue()) {
            if ($image[0]) {
              $data = !empty($image[0]['target_id']) ? ProgramUtility::getImageUri($image[0]['target_id']) : "";
            }
            $organisation[$gid]['orgImage'] = !empty($data) ?
              ProgramUtility::generateOrganisationImage($data) : "";
          }
          $organisation[$gid]['orgPath'] = !empty($gid) ?
          "/dashboard/save-program/$gid" : "#";

          $organisation[$gid]['programUrl'] = \Drupal::service('path.alias_manager')->getAliasByPath("/group/$gid");
          $organisation[$gid]['target'] = "_self";
          $group_domain = \Drupal::entityTypeManager()->getStorage('domain')->load('group_' . $gid);
          if ($group_domain) {
            $organisation[$gid]['programUrl'] = $group_domain->getPath();
            $organisation[$gid]['target'] = "_blank";
          }
        }

        if ($program_id = \Drupal::service('odp_domain.program_domain')->getProgramDomainId()) {
          $program[$program_id] = $organisation[$program_id];
          return $program;
        }
        return $organisation;
      }
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('developer-portal-organisation');
      $logger->error($e->getMessage());
    }
  }

  /**
   * Get Program by node.
   */
  public function getProgramByNid($nid) {
    $query = $this->connection->select('group_content_field_data', 'gcfd')
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
