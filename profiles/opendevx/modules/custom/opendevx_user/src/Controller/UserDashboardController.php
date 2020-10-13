<?php

namespace Drupal\opendevx_user\Controller;

use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\opendevx_user\ProgramInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\opendevx_user\Organisation as Program;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserDashboardController extends ControllerBase {

  /**
   * UserOrganisation object.
   *
   * @var \Drupal\opendevx_user\Organisation
   *
   */
  private $programService;

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * @var AccountInterface $account
   */
  protected $account;

  /**
   * UserDashboardController constructor.
   *
   * @param Program $program
   *   The plugin organisation class.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param Drupal\Core\Session\AccountInterface $account
   *   The plugin account service
   */
  public function __construct(Program $program, RequestStack $request_stack,
  AccountInterface $account) {
    $this->programService = $program;
    $this->currentPath = $request_stack;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('opendevx_user.organisation'),
      $container->get('request_stack'),
      $container->get('current_user')
    );
  }

  /**
   * UserDashboardController callback.
   */
  public function userDashboard() {
    $referer = $this->currentPath->getCurrentRequest()->headers->get('referer');
    $regex  = '/login/';
    if (preg_match($regex, $referer) == TRUE) {
      $tempstore = \Drupal::service('tempstore.private');
      // Get the store collection.
      $store = $tempstore->get('opendevx_block');
      // Get the key/value pair.
      $pid = $store->get('store_pid');
      $path = $store->get('store_path');
      if (!empty($pid) && !empty($path)) {
        $redirect_path = $path ."?pid=" . $pid;
        $response = new RedirectResponse($redirect_path);

        return $response;
      }
      return [];
    }

    // Return empty array.
    return [];
  }

  /**
   * Redirect user after changing program from program switcher.
   */
  public function redirectAfterProgramSave() {
    $path = $this->currentPath->getCurrentRequest()->getPathInfo();
    $referrer = $this->currentPath->getCurrentRequest()->headers->get('referer');

    // Get program id from URL.
    $explode_path = explode('/', $path);
    $program_id = $explode_path[3];
    $this->programService->setProgramId($program_id);
    $group_roles = $this->programService->getUserGroupRole($program_id);
    if (!empty($group_roles)) {
      if (in_array($group_roles[0], ProgramInterface::PM_ROLES)) {
        $referrer = '/dashboard/'. $program_id .'/products';
      }
      else if (in_array($group_roles[0], ProgramInterface::DEV_ROLES)) {
        $referrer = '/dashboard';
      }
    }

    return new RedirectResponse($referrer);
  }

}
