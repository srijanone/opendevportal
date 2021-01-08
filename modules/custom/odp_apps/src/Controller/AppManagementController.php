<?php

namespace Drupal\odp_apps\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class AppManagementController.
 */
class AppManagementController extends ControllerBase {

  /**
   * Object RequestStack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AppManagementController constructor.
   *
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManagerInterface.
   */
  public function __construct(RequestStack $request_stack,
  EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
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
    $node_title = $this->updategalleryfield(1);
    $response = new RedirectResponse($this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER'));
    $response->send();

    $this->messenger()->addMessage($this->t('Application <b>@title</b> is added to the gallery.', ['@title' => $node_title]));
    return $response;
  }

  /**
   * This function is used to unset the gallery field value in applications.
   */
  public function removefromgallery() {
    $node_title = $this->updategalleryfield(0);
    $response = new RedirectResponse($this->requestStack->getCurrentRequest()->server->get('HTTP_REFERER'));
    $response->send();

    $this->messenger()->addMessage($this->t('Application <b>@title</b> is removed from the gallery.', ['@title' => $node_title]));
    return $response;
  }

  /**
   * Update the gallery field data.
   */
  private function updategalleryfield($value) {
    $path = $this->requestStack->getCurrentRequest()->getPathInfo();
    if (strpos($path, '/') !== FALSE) {
      $path_index = explode('/', $path);
      $nid = (int) $path_index[2];
      if (isset($nid) && $node = $this->entityTypeManager->getStorage('node')->load($nid)) {
        $node->set('field_add_to_gallery', $value);
        $node->save();
        return $node->label();
      }
    }
  }

}
