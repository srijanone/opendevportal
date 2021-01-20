<?php

namespace Drupal\odp_subscription;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;

/**
 * Class provides handler for Subscribe and Unsubscribe features.
 */
class SubscriptionContent implements SubscriptionContentInterface {

  /**
   * Subscription reference field name in user profile.
   */
  const SUBSCRIPTION_FIELD_NAME = 'field_subscribed_products';

  /**
   * Member variable for user account.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $account;

  /**
   * Connection object.
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
   * Class constructor.
   */
  public function __construct(
    Connection $connection,
    EntityTypeManagerInterface $entity_type_manager) {
    $this->account = User::load(\Drupal::currentUser()->id());
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubscription(int $nid) {
    // Check if user already have the subscription.
    $subscribed_products = $this->userHasSubscription($nid, $this->account);
    if ($subscribed_products !== TRUE) {
      $subscribed_products[] = ['target_id' => $nid];
      $this->account->set(self::SUBSCRIPTION_FIELD_NAME, $subscribed_products);
      $this->account->save();

      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteSubscription(int $nid) {
    // Check if user already have the subscription.
    $subscribed_products = $this->userHasSubscription($nid, $this->account);
    if ($subscribed_products !== TRUE) {
      return FALSE;
    }
    $subscribed_products = $this->getSubscribedContent($this->account->id());
    foreach ($subscribed_products as $key => $product) {
      if ($product['target_id'] == $nid) {
        unset($subscribed_products[$key]);
      }
    }
    $this->account->set(self::SUBSCRIPTION_FIELD_NAME, $subscribed_products);
    $this->account->save();

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscribedUsers(int $nid) {
    try {
      $query = $this->connection->select('user__' . self::SUBSCRIPTION_FIELD_NAME, 'sfn');
      $query->fields('sfn', ['entity_id'])
        ->condition('sfn.field_subscribed_products_target_id', $nid, '=');
      $result = $query->execute()->fetchCol();
      if (empty($result)) {
        return FALSE;
      }

      return $result;
    }
    catch (\Exception $e) {
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubscribedContent(int $uid = 0) {
    $user = $this->account;

    if ($uid && $uid != $user->id()) {
      $user = $this->entityTypeManager->getStorage('user')->load($uid);
    }
    if ($user->hasField(self::SUBSCRIPTION_FIELD_NAME)) {
      $subscribed_products = $user->get(self::SUBSCRIPTION_FIELD_NAME)->getValue();

      return $subscribed_products ?: [];
    }

    return [];
  }

  /**
   * Helper function to check if user already has the subscription.
   *
   * @return mixed
   *   Return TRUE if  user has the same subscription content otherwise all
   *   subscribed contents.
   */
  public function userHasSubscription(int $nid, User $account) {
    $subscribed_products = $this->getSubscribedContent($account->id());
    return (empty($subscribed_products) ||
      !in_array($nid,
      array_column($subscribed_products, 'target_id')))
      ? $subscribed_products : TRUE;
  }

}
