<?php

namespace Drupal\odp_notification\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Session\AccountInterface;
use Drupal\odp_user\Organisation;
use Drupal\notifications_widget\Services\NotificationsWidgetService;
use Drupal\odp_user\Logger\Logger;

/**
 * Opendevx Notification Utility class.
 */
class OpendevxNotificationService {

  /**
   * Connection variable.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Current User variable.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Configuration variable.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Organisation Variable.
   *
   * @var \Drupal\odp_user\Organisation
   */
  protected $org;

  /**
   * Notificatin loggger.
   *
   * @var \Drupal\notifications_widget\Services\NotificationsWidgetService
   */
  protected $notificationLogger;

  /**
   * Opendevx Logger Service.
   *
   * @var \Drupal\odp_user\Logger\Logger
   */
  protected $logger;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    AccountInterface $currentUser,
    Connection $connection,
    ConfigFactory $configuration,
    Organisation $organisation,
    NotificationsWidgetService $noficationLogger,
    Logger $logger) {
    $this->config = $configuration;
    $this->currentUser = $currentUser;
    $this->connection = $connection;
    $this->org = $organisation;
    $this->notificationLogger = $noficationLogger;
    $this->logger = $logger;
  }

  /**
   * Callback to log notification.
   */
  public function sendNotification($entity) {

    if (empty($entity->bundle()) || $entity->getEntityTypeId() != 'node') {
      return;
    }

    $opendevxConfig = $this->config->get('odp_notification.settings');

    // Get the workflow as per bundle.
    $bundle = $entity->bundle();
    $workflow = $opendevxConfig->get($bundle);

    // Return if notification not enable for bundle.
    if (!in_array($bundle, $opendevxConfig->get('workflow_type')) || empty($workflow)) {
      return;
    }

    // Get the moderation state.
    $moderation_state = $entity->hasField('moderation_state') ? $entity->moderation_state->getValue()[0]['value'] : NULL;

    // Prepare message link data.
    $message                 = [];
    $message['id']           = $entity->id();
    $message['bundle']       = $bundle;
    $message['content']      = $opendevxConfig->get($workflow . '_' . $moderation_state);
    $message['content_link'] = $opendevxConfig->get($moderation_state . '_redirect_link');

    $this->notificationLogger->logNotification($message, 'content_moderated', $entity);
  }

  /**
   * Callback to get notification.
   */
  public function getNotification() {
    $uid = $this->currentUser->id();
    $roles = $this->currentUser->getRoles(TRUE);
    $notification = [
      'notificationList' => [],
      'id' => [],
      'unreadCount' => '0',
      'totalCount' => '0',
    ];

    // Get the nids which are not as per role.
    $nid = $this->getNotificationNode($uid, $roles[0], $this->getContentStateByRole($roles[0]));
    if (!$nid) {
      return $notification;
    }

    $configuration = $this->config->get('block.block.opendevxnotificationblock');

    $query = $this->connection->select('notifications', 'n');
    $query->fields('n', ['id', 'entity_id', 'message', 'status']);
    $query->condition('n.entity_id', $nid, 'IN');
    if ($configuration->get('block_notification_type') != NULL
     && $configuration->get('block_notification_type') == 1) {
      // Skip current user updated content notification.
      $query->condition('n.uid', $uid, '<>');
      // Include current user update content.
      if ($configuration->get('block_notification_logs_display') == 0) {
        $query->condition('n.uid', $uid);
      }
    }
    // Get notification for moderate content.
    $query->condition('n.action', 'content_moderated');
    $query->orderBy('n.id', 'DESC');
    $res = $query->execute();

    while ($notificationResult = $res->fetchObject()) {
      if (!empty($notificationResult->message) && !in_array($notificationResult->entity_id, $notification['id'])) {
        $notification['notificationList'][] = [
          'id'      => $notificationResult->id,
          'message' => $notificationResult->message,
          'status'  => $notificationResult->status,
        ];
        $notification['id'][] = $notificationResult->entity_id;
        if ($notificationResult->status == 0) {
          $notification['unreadCount'] = $notification['unreadCount'] + 1;
        }
        $notification['totalCount'] = $notification['totalCount'] + 1;
      }
    }

    return $notification;
  }

  /**
   * Callback function to get the nid.
   *
   * @param int $uid
   *   Current user id.
   * @param string $role
   *   Current user role.
   * @param array $state
   *   Array of content state.
   *
   * @return array
   *   Array of nid.
   */
  protected function getNotificationNode($uid, $role, array $state) {
    $latest = [];
    $nid = [];
    $query = $this->connection->select('content_moderation_state_field_revision',
     'cmsf');
    $query->fields('cmsf', [
      'content_entity_id', 'revision_id', 'moderation_state',
    ]);
    $query->addJoin('inner', 'node', 'n',
       'cmsf.content_entity_id = n.nid');
    $query->fields('n', ['type']);
    if (\Drupal::service('odp_user.organisation')->checkAccess(TRUE)) {
      $orgid = $this->org->getOrgId();
      $query->addJoin('left', 'group_content_field_data',
        'gcfd', 'gcfd.entity_id = cmsf.content_entity_id');
      $query->condition('gcfd.gid', $orgid);
    }
    else {
      $query->addJoin('left', 'node_field_data', 'nfd',
       'cmsf.content_entity_id = nfd.nid');
      $query->condition('nfd.uid', $uid);
      $query->condition('cmsf.uid', $uid);
    }
    $query->orderBy('revision_id', 'DESC');
    $res = $query->execute();
    // Prepare array of nid which are not in pending for approval state.
    while ($result = $res->fetchObject()) {
      if (!in_array($result->content_entity_id, $latest)
        && in_array($result->moderation_state, $state[$result->type])) {
        $nid[] = $result->content_entity_id;
      }
      $latest[] = $result->content_entity_id;
    }
    return $nid;
  }

  /**
   * Get content moderation state as per role.
   *
   * @param string $role
   *   Role of current user.
   */
  protected function getContentStateByRole($role) {
    $states = [];
    switch (\Drupal::service('odp_user.organisation')->checkAccess(TRUE)) {
      case TRUE:
        $states['api_document'] = [
          'architecture_review',
          'product_review',
          'draft',
          'published',
        ];
        $states['apps'] = [
          'draft',
          'pending_for_approval',
          'published',
        ];

        break;

      case FALSE:
        $states['apps'] = ['published', 'pending_for_approval'];
        break;
    }

    return $states;
  }

  /**
   * Update notification status.
   *
   * @param object $entity
   *   Entity Object.
   * @param bool $status
   *   Status value.
   */
  public function updateNotificationStatus($entity, $status = 0) {
    if ($entity->getEntityTypeId() != 'node') {
      return;
    }
    try {
      $this->connection->update('notifications')
        ->fields(['status' => $status])
        ->condition('entity_id', $entity->id())
        ->condition('action', 'content_moderated')
        ->execute();
    }
    catch (\Exception $e) {
      $this->logger->log(
        ['module' => 'odp_notification', 'message' => $e->getMessage()]
      );
    }
  }

}
