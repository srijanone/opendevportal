<?php

namespace Drupal\opendevx_user\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\opendevx_user\Organisation;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserDashboardController extends ControllerBase {

  /**
   * UserOrganisation object.
   *
   * @var \Drupal\opendevx_user\UserOrganisation $org
   *
   */
  private $org;

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
   * @param mixed $organisation
   *   The plugin organisation class.
   * @param mixed $request_stack
   *   The plugin request stack service.
   * @param mixed $account
   *   The plugin account service
   */
  public function __construct(Organisation $organisation, RequestStack $request_stack,
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
   * redirectAfterOrganisationSave callback.
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
