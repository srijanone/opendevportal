<?php

namespace Drupal\odp_notification\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\odp_notification\Services\OdpNotificationService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Change notification message status.
 *
 * @Action(
 *   id = "odp_notification_bulk_action",
 *   label = @Translation("Mark Notification"),
 *   type = "",
 * )
 */
class OdpNotificationAction extends ViewsBulkOperationsActionBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The NotificationsService.
   *
   * @var \Drupal\odp_notification\Services\OdpNotificationService
   */
  protected $odp_notification;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    OdpNotificationService $odp_notification) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->odp_notification = $odp_notification;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('odp_notification.notification'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity->hasField('nid')) {
      return;
    }
    // Update the notification status.
    $this->odp_notification->updateNotificationStatus($entity, 1);
    return $this->t('Updated');
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

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

}
