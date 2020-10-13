<?php

namespace Drupal\opendevx_user;

use Drupal\group\GroupMembership;
use Drupal\Core\Database\Connection;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\opendevx_organisation\Organisation as Programs;

/**
 * Class Organisation.
 *
 * @package Drupal\opendevx_user
 */
class Organisation extends Programs {

  use LoggerChannelTrait;


  /**
   * Current user organisation.
   *
   * @var mixed
   */
  protected $organisation;

  /**
   * Programs.
   *
   * @var mixed
   */
  protected $programs;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store,
    Connection $connection,
    AccountInterface $account,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->account = $account;
    $this->organisation = $temp_store->get('opendevx_user');
    $this->connection = $connection;
    $this->programs = $this->getOrganisationsData();
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Set the organisation id.
   *
   * @param int $value
   *   Organisation id for current user.
   */
  public function setProgramId($value) {
    $this->organisation->set('org_id', (int) $value);
    return $this;
  }

  /**
   * Get the organisation id.
   *
   * @return int
   *   Organisation ID.
   */
  public function getOrgId() {
    return $this->organisation->get('org_id') ?: 0;
  }

  /**
   * Function to get user's organisation.
   *
   * @return mixed
   *   User organisation.
   */
  public function getUserOrganisations() {
    // $uid = (int) $this->account->id();
    try {
      // TODO: Once codebase is stable will remove this.
      // $query = $this->connection->select('user__field_organisation', 'ufa');
      // $query->addField('ufa', 'field_organisation_target_id', 'org_id');
      // $query->addJoin('left', 'taxonomy_term_field_data',
      // 'ttfd', 'ttfd.tid = ufa.field_organisation_target_id');
      // $query->condition('ufa.entity_id', $uid, '=');
      // $result = $query->distinct()->execute()->fetchAll();
      // if (!empty($result)) {
      // foreach ($result as $value) {
      // $organisation = $this->programs;
      // }
      // }.
      return $this->programs;
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('devportal-user-block');
      $logger->error($e->getMessage());
    }
  }

  /**
   * Function to get organization roles.
   *
   * @param bool $strict
   *   Boolean param to specify strict check.
   *
   * @return array
   *   Return roles array.
   */
  public function getAdminRoles($strict = FALSE) {
    if ($strict === FALSE) {
      return [
        'admin',
        'product_manager',
      ];
    }
    return [
      'admin',
    ];
  }

  /**
   * Get user's role in a group.
   *
   * @param int $group_id
   *   Group id.
   *
   * @return array
   *   Return array of roles in a group.
   */
  public function getUserGroupRole($group_id) {
    $group_roles = [];

    $group = $this->entityTypeManager->getStorage('group')->load($group_id);
    if ($group instanceof GroupInterface) {
      $member = $group->getMember($this->account);
      if ($member instanceof GroupMembership) {
        foreach ($member->getRoles() as $grole) {
          if ($grole->isInternal() == FALSE) {
            $group_roles[] = $grole->id();
          }
        }
      }
    }

    return $group_roles;
  }

  /**
   * Check user access, if user is visible or not.
   */
  public function checkAccess($check_pm = FALSE) {
    $diff = array_intersect(ProgramInterface::ADMIN_ROLES, $this->account->getRoles());
    if (count($diff) > 0) {
      return TRUE;
    }

    if ($check_pm) {
      $roles = array_intersect(ProgramInterface::PM_ROLES, $this->getUserGroupRole($this->getOrgId()));
      if (count($roles) > 0) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
