<?php

namespace Drupal\opendevx_subscription;

use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface defining an subscription flow.
 */
interface SubscriptionQueueInterface {

  /**
   * Sets the content to the notifications list.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity on which operation performed.
   *
   * @return entity
   *   The entity on which operation performed.
   */
  public function setQueueRecords(EntityInterface $entity);

  /**
   * Returns DB records from table opendevx_subscription.
   *
   * @return array
   *   The array of content IDs.
   */
  public function getQueueRecords(int $limit, int $offset);

  /**
   * Updates DB records in table opendevx_subscription.
   *
   * @return int
   *   The processed ID.
   */
  public function updateQueueRecord(int $id);

  /**
   * Delete DB records from table opendevx_subscription.
   *
   * @return bool
   *   The TRUE on success.
   */
  public function deleteQueueRecord(int $id);

}
