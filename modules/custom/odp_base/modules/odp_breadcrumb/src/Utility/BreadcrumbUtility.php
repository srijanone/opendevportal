<?php

namespace Drupal\odp_breadcrumb\Utility;

use Drupal\Core\Link;
use Drupal\views\Views;
use Drupal\Core\Url;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\odp_breadcrumb\BreadcrumbInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\odp_user\Organisation as Program;
use Drupal\Core\Entity\EntityRepositoryInterface;

/**
 * Class to extend and provide the features of customizing breadcrumbs.
 */
class BreadcrumbUtility {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Represents the current path for the current request.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * The node objcet.
   *
   * @var node
   */
  protected $node = NULL;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Program Service.
   *
   * @var Drupal\odp_user\Organisation
   */
  protected $programService;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructor function.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity object.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The crruent route match stack.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param Drupal\odp_user\Organisation $program_service
   *   Program service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(CurrentPathStack $current_path,
   RequestStack $request_stack,
   EntityTypeManagerInterface $entity_type_manager,
   CurrentRouteMatch $route_match,
   AccountInterface $current_user,
   Program $program_service,
   EntityRepositoryInterface $entity_repository) {
    $this->currentPath = $current_path;
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->currentUser = $current_user;
    $this->programService = $program_service;
    $this->entityRepository = $entity_repository;
  }

  /**
   * Get the view title.
   *
   * @param string $view_id
   *   View id.
   * @param string $display_id
   *   View display id.
   *
   * @return string
   *   View title.
   */
  protected function getViewTitle($view_id, $display_id) {
    $view = Views::getView($view_id);
    $view->setDisplay($display_id);
    return $view->getTitle();
  }

  /**
   * Set node by path.
   */
  public function setNodeByPath() {
    $index = explode('/', $this->currentPath->getPath());
    if (!isset($index[2])) {
      $this->setNode('');
      return;
    }
    $node = $this->entityTypeManager->getStorage('node')->load($index[2]);
    $this->setNode($node);
  }

  /**
   * Set node object.
   *
   * @param mixed $node
   *   Node object.
   */
  public function setNode($node) {
    $this->node = $node;
  }

  /**
   * Get node details.
   *
   * @param string $type
   *   Type of request.
   *
   * @return mixed
   *   Return the node parameters.
   */
  public function getNode($type = '') {
    if (empty($this->node)) {
      return '';
    }
    $result = $this->node;
    switch ($type) {
      case 'nid':
        $result = $this->node->id();
        break;

      case 'bundle':
        $result = $this->node->bundle();
        break;
    }
    return $result;
  }

  /**
   * Get the breadcrumb for views.
   *
   * @param object $breadcrumb
   *   Breadcrumb object.
   * @param array $parameters
   *   Route parameter array.
   *
   * @return object
   *   Return breadcrumb object.
   */
  public function getViewBreadcrumb($breadcrumb, array $parameters) {
    // Add cache context for view.
    $breadcrumb->addCacheContexts(["url"]);
    $breadcrumb->addCacheTags(["view_id:{$parameters['view_id']}"]);
    // Check view for breadcrumb.
    if (in_array($parameters['view_id'], BreadcrumbInterface::BREADCRUMB_VIEW_ID)) {
      $path_info = $this->requestStack->getCurrentRequest()->getPathInfo();
      if (in_array('admin', $this->currentUser->getRoles(TRUE))) {
        $menu_breadcrumb = $this->getParentMenuInfo(BreadcrumbInterface::SITEADMIN_MENU_NAME, $path_info);
      }
      elseif ($this->programService->checkAccess(TRUE)) {
        $path_info_data = explode('/', $path_info);
        $path_info_data[2] = '[program:program_id]';
        $path_info = implode('/', $path_info_data);
        $menu_breadcrumb = $this->getParentMenuInfo(BreadcrumbInterface::PRODUCT_MANAGER_MENU_NAME, $path_info);
      }
      else {
        $menu_breadcrumb = $this->getParentMenuInfo(BreadcrumbInterface::DEVELOPER_MENU_NAME, $path_info);
      }
      if ($menu_breadcrumb && !empty($menu_breadcrumb['parent_name'])) {
        $breadcrumb->addLink(Link::createFromRoute($menu_breadcrumb['parent_name'], '<nolink>'));
      }
    }

    $breadcrumb->addLink(Link::createFromRoute($this->getViewTitle($parameters['view_id'], $parameters['display_id']), '<nolink>'));
    if ($this->programService->checkAccess(TRUE) && (!empty($path_info_data)
      && isset($path_info_data) && $path_info_data[3] == 'contents') &&
      !empty($path_info_data[4])) {
      $breadcrumb->addLink(Link::createFromRoute(BreadcrumbInterface::CONTENT_TYPE_NAME_MAPPING[$path_info_data[4]], '<nolink>'));
    }

    return $breadcrumb;
  }

  /**
   * Get the breadcrumb for node.
   *
   * @param object $breadcrumb
   *   Breadcrumb object.
   * @param array $parameters
   *   Route parameter array.
   *
   * @return object
   *   Return breadcrumb object.
   */
  public function getNodeBreadcrumb($breadcrumb, array $parameters) {
    $node = (isset($parameters['node']) && is_object($parameters['node'])) ? $parameters['node'] : $this->getNode();
    $node_type = $node->bundle();
    $path_index = explode('/', $this->currentPath->getPath());
    $breadcrumb->addLink(Link::fromTextAndUrl('Products', Url::fromUri('base:products')));
    // Check if the bundle is not a Landing Page.
    if ($node_type != 'listing_pages') {
      $params = $this->requestStack->getCurrentRequest()->get('parent');
      if ($params) {
        $node_storage = $this->entityTypeManager->getStorage('node');
        $product_node = $node_storage->load($params);
        $breadcrumb->addLink(Link::fromTextAndUrl($product_node->getTitle(), Url::fromRoute('entity.node.canonical', ['node' => (int) $params])));
        $breadcrumb->addLink(Link::fromTextAndUrl(BreadcrumbInterface::CONTENT_TYPE_NAME_MAPPING[$node_type], Url::fromUri('base:/node/' . $params . '/' . $node_type)));
      }
      if (!empty($path_index[3])) {
        $breadcrumb->addLink(Link::fromTextAndUrl($node->getTitle(), Url::fromRoute('entity.node.canonical', ['node' => (int) $path_index[2]])));
        if (!empty(BreadcrumbInterface::CONTENT_TYPE_NAME_MAPPING[$path_index[3]])) {
          $breadcrumb->addLink(Link::createFromRoute(BreadcrumbInterface::CONTENT_TYPE_NAME_MAPPING[$path_index[3]], '<nolink>'));
        }
        elseif ($path_index[1] == 'subscribe') {
          $link_title = t('Subscribe');
          if ($this->requestStack->getCurrentRequest()->get('op') == 'remove') {
            $link_title = t('Unsubscribe');
          }
          $breadcrumb->addLink(Link::createFromRoute($link_title, '<nolink>'));
        }
      }
      else {
        $breadcrumb->addLink(Link::createFromRoute($node->getTitle(), '<nolink>'));
      }
    }
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

  /**
   * Get the breadcrumb for node.
   *
   * @param string $menu_name
   *   Menu name.
   * @param string $path_info
   *   Path info.
   *
   * @return array
   *   Return menu parent name
   */
  public function getParentMenuInfo($menu_name, $path_info) {
    $menu_links = $this->entityTypeManager->getStorage('menu_link_content')->loadByProperties(['menu_name' => $menu_name]);
    foreach ($menu_links as $menu_link) {
      if ('internal:' . $path_info == $menu_link->link->uri && $menu_link->parent->value) {
        $parent_menu_info = $this->entityRepository->loadEntityByUuid('menu_link_content', explode(':', $menu_link->parent->value)[1]);
        if ($parent_menu_info) {
          $link['parent_name'] = $parent_menu_info->getTitle();
        }
      }
    }

    return $link ?? [];
  }

}
