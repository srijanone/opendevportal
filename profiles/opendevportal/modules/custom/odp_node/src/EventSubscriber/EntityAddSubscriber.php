<?php

namespace Drupal\odp_node\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Access Denied node page.
 */
class EntityAddSubscriber implements EventSubscriberInterface {

  /**
   * Entity add page check.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function entityAddRedirection(GetResponseEvent $event) {
    $request = $event->getRequest();
    $current_user_roles = \Drupal::currentUser()->getRoles();

    // Access Denied the node/add page.
    if (($request->get('_route') == 'node.add_page') ||
     ($request->get('_route') == 'node.add' &&
      $request->getPathInfo() != '/node/add/gateway')
    ) {
      throw new AccessDeniedHttpException();
    }

    if (!$request->query->get('destination') && in_array('admin', $current_user_roles)) {
      switch ($request->get('_route')) {
        case "entity.group.add_form":
          $event->setResponse(new RedirectResponse($request->getRequestUri() . '?destination=dashboard/manage/program'));
          break;

        case "entity.group_content.create_form":
          $path = $request->getPathInfo();
          $arg  = explode('/', $path);
          $event->setResponse(new RedirectResponse($request->getRequestUri() . "?destination=dashboard/manage/content/$arg[2]"));
          break;
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['entityAddRedirection', -1000];
    return $events;
  }

}
