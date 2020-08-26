<?php

namespace Drupal\opendevx_user;

use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\opendevx_organisation\Organisation as Programs;
use Drupal\opendevx_organisation\Utility\OrganisationUtility;

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
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store,
    Connection $connection,
    AccountInterface $account) {
    $this->account = $account;
    $this->organisation = $temp_store->get('opendevx_user');
    $this->connection = $connection;
    $this->programs = $this->getOrganisationsData();
  }

  /**
   * Set the organisation id.
   *
   * @param int $value
   *   Organisation id for current user.
   */
  public function setOrgId($value) {
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
    return $this->organisation->get('org_id');
  }

  /**
   * Function to get user's organisation.
   *
   * @return mixed
   *   User organisation.
   */
  public function getUserOrganisations() {
    $uid = (int) $this->account->id();
    try {
      $query = $this->connection->select('user__field_organisation', 'ufa');
      $query->addField('ufa', 'field_organisation_target_id', 'org_id');
      $query->addJoin('left', 'taxonomy_term_field_data',
      'ttfd', 'ttfd.tid = ufa.field_organisation_target_id');
      $query->condition('ufa.entity_id', $uid, '=');
      $result = $query->distinct()->execute()->fetchAll();
      if (!empty($result)) {
        foreach ($result as $value) {
          $organisation[$value->org_id] = $this->programs[$value->org_id];
        }
      }
     
      return $organisation;
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('devportal-user-block');
      $logger->error($e->getMessage());
    }
  }

  /**
   * Function to get organization roles.
   *
   * @param boolean $strict
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

}
