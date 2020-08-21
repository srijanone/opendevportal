<?php

namespace Drupal\opendevx_apps\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides controller class AppManagementController.
 */
class AppManagementController extends ControllerBase {

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The node id.
   *
   * @var int
   */
  private $nid;

  /**
   * The previous URL.
   *
   * @var mixed
   */
  private $previousUrl;

  /**
   * AppManagementController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManagerInterface.
   */
  public function __construct(RequestStack $request_stack,
  EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $path = $request_stack->getCurrentRequest()->getPathInfo();
    $path_index = explode('/', $path);
    $this->nid = (int) $path_index[2];
    $this->previousUrl = \Drupal::request()->server->get('HTTP_REFERER');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * This function is used to set the gallery field value in applications.
   */
  public function addtogallery() {
    $this->updategalleryfield(1);
    $response = new RedirectResponse($this->previousUrl);
    $response->send();

    return [];
  }

  /**
   * This function is used to unset the gallery field value in applications.
   */
  public function removefromgallery() {
    $this->updategalleryfield(0);
    $response = new RedirectResponse($this->previousUrl);
    $response->send();

    return [];
  }

  /**
   * Update the gallery field data.
   */
  private function updategalleryfield($value) {
    $node = $this->entityTypeManager->getStorage('node')->load($this->nid);
    if ($node) {
      $node->set('field_add_to_gallery', $value);
      $node->save();
    }
  }

}
