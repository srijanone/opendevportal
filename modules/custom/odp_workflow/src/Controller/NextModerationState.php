<?php

namespace Drupal\odp_workflow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\odp_notification\Services\OdpNotificationService;
use Drupal\odp_user\Organisation;
use Drupal\odp_domain\Utility\Program\ProgramUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NextModerationState.
 *
 * Moderation State customization.
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
   * The NotificationsService.
   *
   * @var \Drupal\odp_notification\Services\OdpNotificationService
   */
  protected $notification;

  /**
   * Organisation object.
   *
   * @var \Drupal\odp_user\Organisation
   *
   */
  protected $org;

  /**
   * ProgramUtility object.
   *
   * @var \Drupal\odp_domain\Utility\Program\ProgramUtility
   *
   */
  protected $programUtility;

  /**
   * UserDashboardController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   EntityTypeManagerInterface.
   * @param \Drupal\odp_notification\Services\OdpNotificationService $notificationService
   *   OdpNotificationService.
   * @param \Drupal\odp_user\Organisation $org
   *   Organisation.
   * @param \Drupal\odp_domain\Utility\Program\ProgramUtility $programUtility
   *   ProgramUtility.
   */
  public function __construct(RequestStack $request_stack,
                              EntityTypeManagerInterface $entity_type_manager,
                              OdpNotificationService $notificationService,
                              Organisation $organisation,
                              ProgramUtility $program_utility) {
    $this->currentPath = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->notification = $notificationService;
    $this->org = $organisation;
    $this->programUtility = $program_utility;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('odp_notification.notification'),
      $container->get('odp_user.organisation'),
      $container->get('odp_domain.program_utility')
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
        // Only use the service if the module is enabled.
        if (\Drupal::service('module_handler')->moduleExists('odp_notification')) {
          $this->notification->sendNotification($node);
        }
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
    $program_id = $this->org->getOrgId();
    return $this->t('Add Member to @program_name', ['@program_name' => $this->programUtility->getProgramName($program_id)]);
  }

  /**
   * Change the doamin settings page title.
   */
  public function getDomainSettingTitle() {
    return $this->t('Domain Member Settings');
  }

}
