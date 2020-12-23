<?php

namespace Drupal\odp_node\Routing;

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
      $route->setDefault(
        '_title_callback',
        'Drupal\odp_node\Controller\NextModerationState::getAddProgramTitle'
      );
    }
    if ($route = $collection->get('entity.group_content.add_form')) {
      $route->setDefault(
        '_title_callback',
        'Drupal\odp_node\Controller\NextModerationState::getMemberPageTitle'
      );
    }
    if ($route = $collection->get('domain_group.domain_group_settings_form')) {
      $route->setDefault(
        '_title_callback',
        'Drupal\odp_node\Controller\NextModerationState::getDomainSettingTitle'
      );
    }
  }

}
