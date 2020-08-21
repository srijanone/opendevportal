<?php

namespace Drupal\opendevx_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\opendevx_user\Organisation;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides controller class UserDashboardController.
 */
class UserDashboardController extends ControllerBase {

  /**
   * UserOrganisation object.
   *
   * @var \Drupal\opendevx_user\Organisation
   */
  private $org;

  /**
   * The request stack instance.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * The current user instance.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * UserDashboardController constructor.
   *
   * @param \Drupal\opendevx_user\Organisation $organisation
   *   The plugin organisation class.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The plugin account service.
   */
  public function __construct(Organisation $organisation,
  RequestStack $request_stack,
  AccountInterface $account) {
    $this->org = $organisation;
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
    $regex = '/login/';
    if (preg_match($regex, $referer) == TRUE) {
      $tempstore = \Drupal::service('tempstore.private');
      // Get the store collection.
      $store = $tempstore->get('opendevx_block');
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
   * RedirectAfterOrganisationSave callback.
   */
  public function redirectAfterOrganisationSave() {
    $path = $this->currentPath->getCurrentRequest()->getPathInfo();
    $referer = $this->currentPath->getCurrentRequest()->headers->get('referer');
    $explode_path = explode('/', $path);
    $org_id = $explode_path[3];
    $this->org->setOrgId($org_id);
    if (in_array('product_manager', $this->account->getRoles())) {
      $response = new RedirectResponse('/dashboard/products');
    }
    else {
      $response = new RedirectResponse($referer);
    }

    return $response;
  }

}
