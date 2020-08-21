<?php

namespace Drupal\opendevx_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Provides controller class CtaController.
 */
class CtaController extends ControllerBase {

  /**
   * The request stack instance.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * The account instance for the current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The temporary path instance.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $storePath;

  /**
   * CtaController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack class.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The plugin account service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The plugin temporary store instance.
   */
  public function __construct(RequestStack $request_stack,
  AccountInterface $account,
  PrivateTempStoreFactory $temp_store) {
    $this->currentPath = $request_stack;
    $this->account = $account;
    $this->storePath = $temp_store->get('developer_portal_block');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('tempstore.private')
    );
  }

  /**
   * Redirect CTA Url callback.
   */
  public function redirectCtaUrl($type, $pid) {
    if ($this->account->isAnonymous() == TRUE) {
      if ($type == 'apps') {
        $url = "/user/login?path=/node/add/apps&pid=$pid";
        $response = new RedirectResponse($url);
        $response->send();
      }
      if ($type = 'issues') {
        $url = "/user/login?path=/node/add/issues&pid=$pid";
        $response = new RedirectResponse($url);
        $response->send();
      }
    }
    else {
      $this->storePath->set('store_pid', $pid);
      if ($type == 'apps') {
        $url = "/node/add/apps";
        $response = new RedirectResponse($url);
        $response->send();
      }
      if ($type = 'issues') {
        $url = "/node/add/issues";
        $response = new RedirectResponse($url);
        $response->send();
      }
    }
  }

}
