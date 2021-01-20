<?php

namespace Drupal\odp_apps\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Database\Connection;
use Drupal\odp_user\Logger\Logger;

/**
 * Provides class CtaAppsController.
 */
class CtaAppsController extends ControllerBase {

  /**
   * The current path.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * The user AccountInterface.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The temp storage.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $storePath;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * OpenDevPortal Logger Service.
   *
   * @var \Drupal\odp_user\Logger\Logger
   */
  protected $logger;

  /**
   * CtaAppsController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The temp storage service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection service.
   * @param \Drupal\odp_user\Logger\Logger $logger
   *   The logger service.
   */
  public function __construct(RequestStack $request_stack,
  AccountInterface $account,
  PrivateTempStoreFactory $temp_store,
  Connection $connection,
  Logger $logger) {
    $this->currentPath = $request_stack;
    $this->account = $account;
    $this->storePath = $temp_store->get('odp_block');
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
      $container->get('odp_user.logger')
    );
  }

  /**
   * Redirect CTA Url callback.
   */
  public function redirectCtaUrl($pid) {
    $gid = $this->getGroupIdByNodeId($pid);
    if ($this->account->isAnonymous() == TRUE) {
      $url = "/user/login?path=/group/$gid/content/create/group_node:apps";
      $response = new RedirectResponse($url);
      $response->send();
    }
    else {
      $this->storePath->set('store_pid', $pid);
      $url = "/group/$gid/content/create/group_node:apps";
      $response = new RedirectResponse($url);
      $response->send();
    }

    return $response;
  }

  /**
   * Return the Group Id from the Node Id.
   */
  protected function getGroupIdByNodeId($nid) {
    try {
      $query = $this->connection->select('group_content', 'g');
      $query->addJoin('INNER', 'group_content_field_data', 'gd', 'g.id = gd.id');
      $query->addField('gd', 'gid');
      $query->condition('gd.entity_id', $nid);
      $groups = $query->execute()->fetchAssoc();

      return $groups['gid'];
    }
    catch (\Exception $e) {
      $this->logger->log(
        ['module' => 'odp_apps', 'message' => $e->getMessage()]
      );
    }
  }

}
