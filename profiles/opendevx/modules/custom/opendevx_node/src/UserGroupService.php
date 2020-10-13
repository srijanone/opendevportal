<?php

namespace Drupal\opendevx_node;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\GroupMembershipLoaderInterface;

/**
 * UserGroupService service class to handle group functionalities.
 */
class UserGroupService {

  /**
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
   * Object GroupMembership.
   *
   * @var \Drupal\Group\GroupMembershipLoaderInterface
   */
  protected $groupMembership;

  /**
   * Object GroupStorage.
   */
  protected $groupStorage;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    AccountInterface $account, EntityTypeManagerInterface $entity_type_manager, GroupMembershipLoaderInterface $group_membership) {
    $this->account = $account;
    $this->entityTypeManager = $entity_type_manager;
    $this->groupMembership = $group_membership;
    $this->groupStorage = $this->entityTypeManager->getStorage('group');
  }

  /**
   * Method to Get user group.
   */
  public function getUserGroups() {

    $user = $this->entityTypeManager->getStorage('user')->load($this->account->id());
    $group_storage = $this->entityTypeManager->getStorage('group');
    $gids = $this->groupStorage->getQuery()->condition('type','Public')->execute();
    $groups = $this->groupStorage->loadMultiple($gids);
    $options = ['' => 'All'];
    if ($grps = $this->groupMembership->loadByUser($user)) {
      foreach ($grps as $grp) {
        $group = $grp->getGroup();
        $groups[$group->id()] = $group;
      }
    }

    foreach($groups as $gid => $group) {
      $value = $group->label();

      if (isset($value)) {
        $options[$gid] =  $value;
      }
    }
    return $options;
  }

}
