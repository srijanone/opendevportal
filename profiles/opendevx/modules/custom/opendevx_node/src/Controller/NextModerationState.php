<?php

namespace Drupal\opendevx_node\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NextModerationState.
 */
class NextModerationState extends ControllerBase {

  /**
   * Current path instance.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
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
        'draft' => 'Design',
        'architecture_review' => 'Architecture Review',
        'product_review' => 'Product Review',
        'published' => 'Approved',
      ];
    }
    elseif ($type == 'apps') {
      $workflow = [
        'draft' => 'Draft',
        'pending_for_approval' => 'Approved for Sandbox',
        'published' => 'Approved for Production',
      ];
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
        // Return to the listing page.
        $url = $this->currentPath->getCurrentRequest()->headers->get('referer');
        $response = new RedirectResponse($url);
        $response->send();
      }

      return [];
    }
  }

  /**
   * Change the program title.
   */
  public function getAddProgramTitle() {
    return $this->t('Add Program');
  }

  /**
   * Change the member page title.
   */
  public function getMemberPageTitle() {
    $program_id = \Drupal::service('opendevx_user.organisation')->getOrgId();
    $program_type = \Drupal::service('opendevx_core.program_utility')->getProgramType($program_id);
    return $this->t('Add @program_type : Member membership', ['@program_type' => $program_type]);
  }

  /**
   * Change the doamin settings page title.
   */
  public function getDomainSettingTitle() {
    return $this->t('Domain Member Settings');
  }

}
