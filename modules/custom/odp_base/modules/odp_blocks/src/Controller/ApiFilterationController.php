<?php

namespace Drupal\odp_blocks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\odp_blocks\Utility\ApiFilterationUtility;

/**
 * Provides class ApiFilterationController.
 */
class ApiFilterationController extends ControllerBase {

  /**
   * The current path.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * ApiFilterationController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   */
  public function __construct(RequestStack $request_stack) {
    $this->currentPath = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * Get API Names callback.
   */
  public function getApiNames() {
    $environment_id = $this->currentPath->getCurrentRequest()->get('environment_id');
    $parent = $this->currentPath->getCurrentRequest()->get('parent_id');
    $names = ApiFilterationUtility::getApiNames($environment_id, $parent);

    return new JsonResponse($names);
  }

  /**
   * Get API Versions callback.
   */
  public function getApiVersions() {
    $version_id = $this->currentPath->getCurrentRequest()->get('api_id');
    $result = ApiFilterationUtility::getApiVersions($version_id);

    return new JsonResponse($result);
  }

}
