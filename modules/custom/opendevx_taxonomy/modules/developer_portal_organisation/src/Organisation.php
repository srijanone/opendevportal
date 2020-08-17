<?php

namespace Drupal\developer_portal_organisation;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\developer_portal_organisation\Utility\OrganisationUtility;

/**
 * Class Organisation.
 *
 * @package Drupal\developer_portal_organisation
 */
class Organisation {

  use LoggerChannelTrait;

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
    Connection $connection,
    AccountInterface $account) {
    $this->account = $account;
    $this->connection = $connection;
  }

  /**
   * Function to get organisation information.
   *
   *
   * @return array
   *    Organisation data.
   */
  public function getOrganisationsData() {
    try {
      $query = $this->connection->select('taxonomy_term_field_data', 'ttfd');
      $query->fields('ttfd', ['tid', 'name']);
      $query->addJoin('left', 'taxonomy_term__field_organisation_images',
      'ttfi', 'ttfi.entity_id = ttfd.tid');
      $query->addJoin('left', 'media__field_media_image', 'mfm',
      'mfm.entity_id = ttfi.field_organisation_images_target_id');
      $query->addJoin('left', 'file_managed', 'fm',
      'fm.fid = mfm.field_media_image_target_id');
      $query->addJoin('left', 'taxonomy_term__field_organisation_page', 'ttfp',
      'ttfp.entity_id = ttfd.tid');
      $query->addField('fm', 'uri', 'image_uri');
      $query->addField('ttfp', 'field_organisation_page_target_id', 'pid');
      $query->condition('ttfd.vid', 'organisation', '=');
      $result = $query->distinct()->execute()->fetchAll();
      if (!empty($result)) {
        $organisation = [];
        foreach ($result as $value) {
          $organisation[$value->tid]['orgId'] = $value->tid;
          $organisation[$value->tid]['orgImage'] = !empty($value->image_uri) ?
          OrganisationUtility::generateOrganisationImage($value->image_uri) : "";
          $organisation[$value->tid]['orgName'] = $value->name;
          $organisation[$value->tid]['orgPath'] = !empty($value->tid) ?
          "/dashboard/save-organisation/$value->tid" : "#";
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
   * Function to get accessable organisation information.
   *
   *
   * @return array
   *    Organisation data.
   */
  public function getOrganisationsDataByAccess() {
    $access = ['Public', 'Protected'];

    try {
      $query = $this->connection->select('taxonomy_term_field_data', 'ttfd');
      $query->fields('ttfd', ['tid', 'name']);
      $query->addJoin('left', 'taxonomy_term__field_organisation_images',
      'ttfi', 'ttfi.entity_id = ttfd.tid');
      $query->addJoin('left', 'media__field_media_image', 'mfm',
      'mfm.entity_id = ttfi.field_organisation_images_target_id');
      $query->addJoin('left', 'file_managed', 'fm',
      'fm.fid = mfm.field_media_image_target_id');
      $query->addJoin('left', 'taxonomy_term__field_organisation_page', 'ttfp',
      'ttfp.entity_id = ttfd.tid');
      $query->addJoin('left', 'taxonomy_term__field_external_link', 'ttfl',
      'ttfl.entity_id = ttfd.tid');
      $query->addJoin('left', 'taxonomy_term__field_refer_to', 'ttfr',
      'ttfr.entity_id = ttfd.tid');
      $query->addJoin('left', 'taxonomy_term__field_access', 'ttfa',
      'ttfa.entity_id = ttfd.tid');
      $query->addField('ttfa', 'field_access_value', 'org_access');
      $query->addField('fm', 'uri', 'image_uri');
      $query->addField('ttfp', 'field_organisation_page_target_id', 'pid');
      $query->addField('ttfl', 'field_external_link_uri', 'eid');
      $query->addField('ttfr', 'field_refer_to_value', 'refer_to');
      $query->condition('ttfd.vid', 'organisation', '=');
      $query->condition('ttfa.field_access_value', $access, 'IN');
      $result = $query->distinct()->execute()->fetchAll();
      if (!empty($result)) {
        $organisation = [];
        foreach ($result as $value) {
          $organisation[$value->tid]['orgImage'] = !empty($value->image_uri) ?
          OrganisationUtility::generateOrganisationImage($value->image_uri) : "";
          $organisation[$value->tid]['orgTitle'] = $value->name;
          $organisation[$value->tid]['referTo'] = $value->refer_to;
          $organisation[$value->tid]['orgAccess'] = strtolower($value->org_access);
          $organisation[$value->tid]['orgRefer'] = $this->getOrganisationPath($value->pid, $value->refer_to, $value->eid);
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
   * Prepare organisation path.
   *
   * @param int $id
   *    Reference id.
   * @param string $refer_to
   *   Type of organisation.
   * @param string $eid
   *   External domain url.
   *
   * @return mixed
   *    Organisation landing path.
   */
  public function getOrganisationPath($id, $refer_to = NULL, $eid = '') {
    // Set $refer url.
    $refer = ($eid && $refer_to === 'external') ? $eid : '#';
    // Set refer url as per organisation id.
    if ($id && $refer_to != '_none') {
      $path = "/node/$id";
      $refer = \Drupal::service('path.alias_manager')->getAliasByPath($path);
      $refer = ($refer_to === 'external') ? $eid . $refer : $refer;
    }

    return $refer;
  }

}
