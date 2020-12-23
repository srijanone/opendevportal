<?php

namespace Drupal\odp_core\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * UnPublishes an entity.
 *
 * @Action(
 *   id = "odp_vbo_unpublished_action",
 *   label = @Translation("Unpublish latest node"),
 *   type = "node",
 *   confirm = TRUE,
 * )
 */
class OpenDevxVboUnpublished extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    $entity->setUnpublished()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }
    return TRUE;
  }

}
