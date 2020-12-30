<?php

namespace Drupal\odp_subscription;

/**
 * Provides an interface defining an subscription flow.
 */
interface SubscriptionContentInterface {

  /**
   * Sets the content to the user's notifications list.
   *
   * @param int $nid
   *   The entity ID on which operation performed.
   *
   * @return bool
   *   The boolean value showing success or failure.
   */
  public function setSubscription(int $nid);

  /**
   * Delete the content from the user subscription list.
   *
   * @param int $nid
   *   The entity ID on which operation performed.
   *
   * @return bool
   *   The boolean value showing success or failure.
   */
  public function deleteSubscription(int $nid);

  /**
   * Returns all subscribed users for an entity by ID.
   *
   * @param int $nid
   *   The entity ID which is subscribed by users.
   *
   * @return array
   *   The array of user IDs.
   */
  public function getSubscribedUsers(int $nid);

  /**
   * Returns all subscribed contents for a user by ID.
   *
   * @param int $uid
   *   A user id of the subscriber.
   *
   * @return array
   *   The array of content IDs.
   */
  public function getSubscribedContent(int $uid);

}
