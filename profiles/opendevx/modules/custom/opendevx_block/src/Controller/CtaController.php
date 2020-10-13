<?php

namespace Drupal\opendevx_block\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Database\Connection;
use Drupal\opendevx_user\Logger\Logger;

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
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Opendevx Logger Service.
   *
   * @var Drupal\opendevx_user\Logger\Logger
   */
  protected $logger;

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
  PrivateTempStoreFactory $temp_store,
  Connection $connection,
  Logger $logger) {
    $this->currentPath = $request_stack;
    $this->account = $account;
    $this->storePath = $temp_store->get('opendevx_block');
    $this->connection = $connection;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('tempstore.private'),
      $container->get('database'),
      $container->get('opendevx_user.logger')

    );
  }

  /**
   * Redirect CTA Url callback.
   */
  public function redirectCtaUrl($type, $pid) {
    $gid = $this->getGroupIdByNodeId($pid);
    if ($this->account->isAnonymous() == TRUE) {
      if ($type == 'apps') {
        $url = "/user/login?path=/group/$gid/content/create/group_node:apps";
        $response = new RedirectResponse($url);
        $response->send();
      }
      if ($type = 'issues') {
        $url = "/user/login?path=/group/$gid/content/create/group_node:issues";
        $response = new RedirectResponse($url);
        $response->send();
      }
    }
    else {
      $this->storePath->set('store_pid', $pid);
      if ($type == 'apps') {
        $url = "/group/$gid/content/create/group_node:apps";
        $response = new RedirectResponse($url);
        $response->send();
      }
      if ($type = 'issues') {
        $url = "/group/$gid/content/create/group_node:issues";
        $response = new RedirectResponse($url);
        $response->send();
      }
    }
  }

  /**
   * Return the Group Id from the Node Id.
   */
  protected function getGroupIdByNodeId($nid) {
    try {
      $query = $this->connection->select('group_content_field_data', 'g');
      $query->addField('g', 'gid');
      $query->condition('g.entity_id', $nid);
      $groups = $query->execute()->fetchAssoc();

      return $groups['gid'];
    }
    catch (\Exception $e) {
      $this->logger->log(
        ['module' => 'opendevx_block', 'message' => $e->getMessage()]
      );
    }
  }

}
