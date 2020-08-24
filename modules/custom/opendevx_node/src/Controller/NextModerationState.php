<?php

namespace Drupal\opendevx_node\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NextModerationState extends ControllerBase {

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * UserDashboardController constructor.
   *
   * @param mixed $request_stack
   *   The plugin request stack service.
   * @param mixed $entity_type_manager
   *   EntityTypeManagerInterface.
   */
  public function __construct(RequestStack $request_stack, 
  EntityTypeManagerInterface $entity_type_manager) {
    $this->currentPath = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
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
   * Save next moderation state callback.
   */
  public function saveNextModerationState($state, int $nid, $type) {
    if ($type == 'api_document') {
      $workflow = [
        'draft' =>  'Design',
        'architecture_review' => 'Architecture Review',
        'product_review' => 'Product Review',
        'published' => 'Approved'
      ]; 
      $url = "/dashboard/apimanager/proxies";
    }
    elseif ($type == 'apps') {
      $workflow = [
        'draft' => 'Draft',
        'pending_for_approval' => 'Approved for Sandbox',
        'published' => 'Approved for Production'
      ];
      $url = "/dashboard/apimanager/apps";
    }
    if (!empty($nid)) {
      $search = $next = $key = $next_state = '';
      $search = array_search($state, array_keys($workflow));
      if ($search < count(array_keys($workflow)) - 1) {
        $next = $search + 1;
        $key = array_keys($workflow);
        $next_state = $key[$next];
        $node = $this->entityTypeManager->getStorage('node')->load($nid);
        $node->set('moderation_state', $next_state);
        $node->save();
        // Return to the listing page
        $response = new RedirectResponse($url);
        $response->send();
      }

      return [];
    }
  }

}
