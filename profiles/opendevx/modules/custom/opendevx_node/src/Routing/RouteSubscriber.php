<?php
/**
 * @file
 * Contains \Drupal\YOUR_CUSTOM_MODULE\Routing\RouteSubscriber.
 */

namespace Drupal\opendevx_node\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.group.add_page')) {
     $route->setDefault('_title_callback','Drupal\opendevx_node\Controller\NextModerationState::getAddProgramTitle');
    }
  }

}
