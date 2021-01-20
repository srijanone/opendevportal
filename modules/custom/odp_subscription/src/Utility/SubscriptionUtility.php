<?php

namespace Drupal\odp_subscription\Utility;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\odp_subscription\Form\SubscriptionSettings;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\odp_subscription\Services\SubscriptionQueue;
use Drupal\odp_subscription\SubscriptionContent;

/**
 * Class to extend and provide the features of developer portal.
 */
class SubscriptionUtility {

  /**
   * A mail manager for sending email.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Current user object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal\Core\Utility\Token definition.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Drupal\odp_subscription\Services\SubscriptionQueue suQueue.
   *
   * @var \Drupal\odp_subscription\Services\SubscriptionQueue
   */
  protected $subQueue;

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
    AccountInterface $current_user,
    Token $token,
    MailManagerInterface $mailManager,
    SubscriptionQueue $subQueue,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->database = $connection;
    $this->currentUser = $current_user;
    $this->configFactory = \Drupal::service('config.factory')->getEditable(SubscriptionSettings::MODULE_KEY . '.settings');
    $this->token = $token;
    $this->subQueue = $subQueue;
    $this->mailManager = $mailManager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Helper function to prepare cron data.
   */
  public function prepareDataForCron() {
    $num_sent = $num_fail = 0;

    $user = $this->currentUser;

    // @ToDo, here the below commented code should
    // populate values from config, but it cant right now.
    // $uresult = $this->configFactory->get('alert_users');
    if (!isset($uresult) || empty($uresult)) {
      // Set up for sending a new batch. Init all variables.
      $result = $this->database->select(SubscriptionQueue::TABLE_NAME, 'n');
      $result->join('node_field_data', 'nd', 'n.entity_id = nd.nid');
      $result->fields('n', ['id', 'entity_id', 'nid', 'status', 'subscribers']);
      $result->condition('n.status', SubscriptionQueue::SUBSCRIPTION_QUEUED);
      $result->condition('nd.status', 1);
      $result->allowRowCount = TRUE;
      $uresult = $result->execute()->fetchAll(\PDO::FETCH_ASSOC);

      \Drupal::configFactory()->getEditable(SubscriptionSettings::MODULE_KEY . '.settings')
        ->set('alert_users', $uresult)
        ->set('num_sent', 0)
        ->set('num_failed', 0)
        ->save();
    }

    $batchlimit = $this->configFactory->get('batch_limit');
    $batchcount = 0;
    // Allow to safely impersonate the recipient so that the node is rendered
    // with correct field permissions.
    $original_user = $user;

    $nids = array_unique(array_column($uresult, 'nid'));
    $entity_ids = array_unique(array_column($uresult, 'entity_id'));
    if (!$nids) {
      return FALSE;
    }
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $entity_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($entity_ids);
    // Start processing DB records.
    foreach ($uresult as $index => $userrow) {
      // Get actual subscribed product NID.
      $node = $nodes[$userrow['nid']];
      // Exits if the batch limit reached.
      if (++$batchcount > $batchlimit) {
        break;
      }
      // Decode the serialized user IDs.
      $user_ids = unserialize($userrow['subscribers']);
      $users = $this->entityTypeManager->getStorage('user')->loadMultiple($user_ids);
      // Process users one by one.
      foreach ($user_ids as $user_id) {
        $userobj = $users[$user_id];
        // Intentionally replacing the Global $user.
        $user = $userobj;
        $upl = $userobj->getPreferredLangcode();

        // Skip to next if user is not allowed to view this node.
        if (FALSE === $this->allowedToAccessContent($node, $userobj)) {
          continue;
        }
        // @ToDo, need provide custom tokens.
        list($subject, $mail_body) = $this->prepareEmailContent($node, $entity_nodes[$userrow['entity_id']], $userobj);

        if ($mail_body) {
          $watchdog_level = $this->configFactory->get('log_messages');
          $params = [
            'body' => $mail_body,
            'subject' => $subject,
          ];
          // Trigger mail.
          if ($this->mailManager->mail('odp_subscription',
            'devportal_portal_subscribe_newsletter', $userobj->getEmail(), $upl,
            $params, TRUE)) {
            if ($watchdog_level == 0) {
              \Drupal::logger(SubscriptionSettings::MODULE_KEY)->notice('User %name (%mail) notified successfully.', [
                '%name' => $userobj->getDisplayName(),
                '%mail' => $userobj->getEmail(),
              ]);
              // Logging, just to debug.
              \Drupal::logger(SubscriptionSettings::MODULE_KEY)->notice('data (%mail).', [
                '%mail' => print_r($mail_body, TRUE),
              ]);
            }
            $num_sent++;
          }
          else {
            $num_fail++;
            if ($watchdog_level == 0) {
              \Drupal::logger(SubscriptionSettings::MODULE_KEY)->notice('User %name (%mail) could not be notified. Mail error.', [
                '%name' => $userobj->getDisplayName(),
                '%mail' => $userobj->getEmail(),
              ]);
            }
          }
        }
      }
      // Update record as processed.
      $this->subQueue->updateQueueRecord($userrow['id']);

      unset($uresult[$index]);

      \Drupal::configFactory()->getEditable(SubscriptionSettings::MODULE_KEY . '.settings')
        ->set('alert_users', $uresult)
        ->save();
    }
    // Restore the original user session.
    $user = $original_user;

    return [$num_sent, $num_fail];
  }

  /**
   * Helper function to get mail content.
   */
  public function prepareEmailContent($node, $entity, $user) {
    $subject = $this->configFactory->get('mail_subject');
    $body = $this->configFactory->get('mail_body');
    // Here entity is the referring content and node is the reference.
    $subject = $this->token->replace($subject, [
      'user' => $user,
      'node' => $entity,
      'dvp-product' => $node,
    ]);
    $body = $this->token->replace($body, [
      'user' => $user,
      'node' => $entity,
      'dvp-product' => $node,
    ]);

    return [
      MailFormatHelper::htmlToText($subject),
      MailFormatHelper::htmlToText($body),
    ];
  }

  /**
   * Helper function to check user access to node.
   */
  protected function allowedToAccessContent($node, $user) {
    $sub_content = new SubscriptionContent();
    if (!$user->hasPermission('administer nodes') && 0 == $node->status->getString()) {
      return FALSE;
    }
    if (!$user->hasPermission('administer nodes') && !in_array($node->getType(), $this->configFactory->get('entity_types'))) {
      return FALSE;
    }

    if (!$this->configFactory->get('include_unpublished') && 0 == $node->status->getString()) {
      return FALSE;
    }

    if (TRUE !== $sub_content->userHasSubscription($node->id(), $user)) {
      return FALSE;
    }

    return TRUE;
  }

}
