<?php

namespace Drupal\opendevx_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class CtaController extends ControllerBase {

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * @var mixed $storePath
   */
  protected $storePath;

  /**
   * CtaController constructor.
   *
   * @param mixed $product
   *   The plugin api product class.
   * @param mixed $request_stack
   *   The plugin request stack service.
   */
  public function __construct(RequestStack $request_stack,
  AccountInterface $account,
  PrivateTempStoreFactory $temp_store) {
    $this->currentPath = $request_stack;
    $this->account = $account;
    $this->storePath = $temp_store->get('opendevx_block');
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
