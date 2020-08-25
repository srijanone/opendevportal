<?php

namespace Drupal\opendevx_breadcrumb\Utility;

use Drupal\Core\Link;
use Drupal\views\Views;
use Drupal\Core\Url;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Class to extend and provide the features of customizing breadcrumbs.
 */
class BreadcrumbUtility implements ContainerInjectionInterface {
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
  protected $pathCurrent;
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
  protected $node = '';

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
   */
  public function __construct(CurrentPathStack $current_path,
   RequestStack $request_stack,
   EntityTypeManagerInterface $entity_type_manager,
   CurrentRouteMatch $route_match) {
    $this->pathCurrent = $current_path;
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('path.current'),
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
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
    $current_path = $this->pathCurrent->getPath();
    $index = explode('/', $current_path);
    if (!isset($index[2])) {
      $this->setNode('');
      return;
    }
    $nid = $index[2];
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node = $node_storage->load($nid);
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
    $view_id = $parameters['view_id'];
    $displayName = $this->getViewTitle($view_id, $parameters['display_id']);
    if ($view_id == 'proxies_listing') {
      $breadcrumb->addLink(Link::createFromRoute('APIs Platform', '<nolink>'));
    }
    elseif ($view_id == 'organisation_users') {
      $breadcrumb->addLink(Link::createFromRoute('Programs Operations', '<nolink>'));
    }
    $breadcrumb->addLink(Link::createFromRoute($displayName, '<nolink>'));
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
    $content_types = [
      'apps' => 'Applications',
      'article' => 'Blogs',
      'assets' => 'Media',
      'document_overview' => 'Page',
      'events' => 'Events',
      'faq' => 'Faqs',
      'forum' => 'Forums',
      'issues' => 'Issues',
      'resources' => 'Resources',
      'solutions' => 'Solutions',
      'tutorials' => 'Tutorials',
      'use_cases' => 'Use Cases',
      'api_document' => 'API',
    ];
    $node = isset($parameters['node']) ? $parameters['node'] : $this->getNode();
    $node_type = $node->bundle();
    $current_path = $this->pathCurrent->getPath();
    $path_index = explode('/', $current_path);
    $breadcrumb->addLink(Link::fromTextAndUrl('Products', Url::fromUri('base:products')));
    // Check if the bundle is not a Landing Page.
    if ($node_type != 'listing_pages') {
      $params = $this->requestStack->getCurrentRequest()->get('parent');
      if ($params) {
        $node_storage = $this->entityTypeManager->getStorage('node');
        $product_node = $node_storage->load($params);
        $breadcrumb->addLink(Link::fromTextAndUrl($product_node->getTitle(), Url::fromRoute('entity.node.canonical', ['node' => (int) $params])));
        $breadcrumb->addLink(Link::fromTextAndUrl($content_types[$node_type], Url::fromUri('base:/node/' . $params . '/' . $node_type)));
      }
      if (!empty($path_index[3])) {
        $breadcrumb->addLink(Link::fromTextAndUrl($node->getTitle(), Url::fromRoute('entity.node.canonical', ['node' => (int) $path_index[2]])));
        $breadcrumb->addLink(Link::createFromRoute($content_types[$path_index[3]], '<nolink>'));
      }
      else {
        $breadcrumb->addLink(Link::createFromRoute($node->getTitle(), '<nolink>'));
      }
    }
    $breadcrumb->addCacheContexts(['route']);

    return $breadcrumb;
  }

}
