<?php

namespace Drupal\odp_navigation_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\odp_navigation_block\Utility\ApiFilterationUtility;

class ApiFilterationController extends ControllerBase {

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * ApiFilterationController constructor.
   *
   * @param mixed $request_stack
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
