<?php

namespace Drupal\odp_subscription\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Path\CurrentPathStack;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Subscription.
 */
class Subscription extends ControllerBase {

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
   * UserDashboardController constructor.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(CurrentPathStack $current_path, RequestStack $request_stack) {
    $this->currentPath = $current_path;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.current'),
      $container->get('request_stack')
    );
  }

  /**
   * Get title of the product.
   */
  public function getTitle() {
    $path_index = explode('/', $this->currentPath->getPath());
    if (!empty($path_index) && $path_index[1] == 'subscribe') {
      $title = $this->t('Subscribe to product');
      if ($this->requestStack->getCurrentRequest()->get('op') == 'remove') {
        $title = $this->t('Unsubscribe to product');
      }
    }

    return $title;
  }

}
