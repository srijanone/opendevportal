<?php

namespace Drupal\odp_user\Controller;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\odp_user\ProgramInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\odp_user\Organisation as Program;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class UserDashboardController for user dashboard features.
 */
class UserDashboardController extends ControllerBase {

  /**
   * UserOrganisation object.
   *
   * @var \Drupal\odp_user\Organisation
   */
  private $programService;

  /**
   * The current path.
   *
   * @var mixed
   */
  protected $currentPath;

  /**
   * The user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * PrivateTempStoreFactory service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * UserDashboardController constructor.
   *
   * @param \Drupal\odp_user\Organisation $program
   *   The plugin organisation class.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param Drupal\Core\Session\AccountInterface $account
   *   The plugin account service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The TempStore factory class.
   */
  public function __construct(Program $program,
  RequestStack $request_stack,
  AccountInterface $account,
  PrivateTempStoreFactory $temp_store) {
    $this->programService = $program;
    $this->currentPath = $request_stack;
    $this->account = $account;
    $this->tempStore = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('odp_user.organisation'),
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('tempstore.private')
    );
  }

  /**
   * UserDashboardController callback.
   */
  public function userDashboard() {
    $referer = $this->currentPath->getCurrentRequest()->headers->get('referer');
    $regex = '/login/';
    if (preg_match($regex, $referer) == TRUE) {
      // Get the store collection.
      $store = $this->tempStore->get('odp_block');
      // Get the key/value pair.
      $pid = $store->get('store_pid');
      $path = $store->get('store_path');
      if (!empty($pid) && !empty($path)) {
        $redirect_path = $path . "?pid=" . $pid;
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
    if (strpos($path, '/') !== FALSE) {
      $explode_path = explode('/', $path);
      $program_id = $explode_path[3];
      $this->programService->setProgramId($program_id);
      $group_roles = $this->programService->getUserGroupRole($program_id);
    }
    if (!empty($group_roles)) {
      if (in_array($group_roles[0], ProgramInterface::PM_ROLES)) {
        $referrer = '/dashboard/' . $program_id . '/products';
      }
      elseif (in_array($group_roles[0], ProgramInterface::DEV_ROLES)) {
        $referrer = '/dashboard';
      }
    }

    return new RedirectResponse($referrer);
  }

}
