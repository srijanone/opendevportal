<?php

namespace Drupal\opendevx_node\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Access class for custom access check on request init.
 */
class Access implements EventSubscriberInterface {

  /**
   * Entity page access check.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Event object.
   */
  public function checkAccess(GetResponseEvent $event) {
    $routes = [
      'layout_builder.overrides.node.view',
      'entity.node.edit_form',
      'entity.node.delete_form',
    ];

    if (FALSE == \Drupal::service('opendevx_user.organisation')->checkAccess(TRUE) &&
      in_array($event->getRequest()->attributes->get('_route'), $routes) &&
      $event->getRequest()->attributes->get('node')->getOwnerId() != \Drupal::currentUser()->id()
    ) {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkAccess', -1000];
    return $events;
  }

}
