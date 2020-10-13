<?php

namespace Drupal\opendevx_login_redirect;

use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Component\Utility\SortArray;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\opendevx_user\Logger\Logger;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\opendevx_user\ProgramInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\opendevx_user\Organisation as Program;

/**
 * Class definition of redirect service.
 */
class Redirect implements RedirectInterface {

  /**
   * The currently active route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The login_redirect_per_role.settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The current active user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Program Service.
   *
   * @var Drupal\opendevx_user\Organisation
   */
  protected $programService;

  /**
   * Current user tempstore.
   *
   * @var Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * Opendevx Logger Service.
   *
   * @var Drupal\opendevx_user\Logger\Logger
   */
  protected $logger;

  /**
   * Object ProgramDomain.
   *
   * @var Drupal\opendevx_core\Program\ProgramDomainInterface
   */
  protected $programDomain;

  /**
   * Constructs a new Login And Logout Redirect Per Role service object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The currently active route match object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current active user.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param Drupal\opendevx_user\Organisation $program_service
   *   Program service.
   * @param Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   Temp Store service.
   * @param Drupal\opendevx_user\Logger\Logger $logger
   *   Logger Service.
   * @param Drupal\opendevx_core\Program\ProgramDomainInterface $program_domain
   *   Program Domain Service.
   */
  public function __construct(RouteMatchInterface $current_route_match,
  RequestStack $request_stack,
  ConfigFactoryInterface $config_factory,
  AccountProxyInterface $current_user,
  Connection $connection,
  Program $program_service,
  PrivateTempStoreFactory $temp_store,
  Logger $logger) {
    $this->currentRouteMatch = $current_route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->config = $config_factory->get('opendevx_login_redirect.settings');
    $this->currentUser = $current_user;
    $this->connection = $connection;
    $this->programService = $program_service;
    $this->tempStore = $temp_store->get('opendevx_user');
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicableOnCurrentPage() {
    switch ($this->currentRouteMatch->getRouteName()) {

      case 'user.reset':
      case 'user.reset.login':
      case 'user.reset.form':
        return FALSE;

      default:
        return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginRedirectUrl() {
    return $this->getRedirectUrl(RedirectInterface::CONFIG_KEY_LOGIN);
  }

  /**
   * {@inheritdoc}
   */
  public function setLoginDestination(AccountInterface $account = NULL) {
    $this->setDestination(RedirectInterface::CONFIG_KEY_LOGIN, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutRedirectUrl() {
    return $this->getRedirectUrl(RedirectInterface::CONFIG_KEY_LOGOUT);
  }

  /**
   * {@inheritdoc}
   */
  public function setLogoutDestination(AccountInterface $account = NULL) {
    $this->setDestination(RedirectInterface::CONFIG_KEY_LOGOUT, $account);
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutConfig() {
    return $this->getConfig(RedirectInterface::CONFIG_KEY_LOGOUT);
  }

  /**
   * {@inheritdoc}
   */
  public function getLoginConfig() {
    return $this->getConfig(RedirectInterface::CONFIG_KEY_LOGIN);
  }

  /**
   * Set "destination" parameter to do redirect.
   *
   * @param string $key
   *   Configuration key (login or logout).
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   User account to set destination for.
   */
  protected function setDestination($key, AccountInterface $account = NULL) {
    $url = $this->getRedirectUrl($key, $account);

    if ($url instanceof Url) {
      $this->currentRequest->query->set('destination', $url->toString());
    }
  }

  /**
   * Return redirect URL related to requested key and current user.
   *
   * @param string $key
   *   Configuration key (login or logout).
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   User account to get redirect URL for.
   *
   * @return \Drupal\Core\Url|null
   *   Redirect URL related to requested key and current user.
   */
  protected function getRedirectUrl($key, AccountInterface $account = NULL) {
    $url = NULL;

    switch ($key) {
      case RedirectInterface::CONFIG_KEY_LOGIN:
        if (!$this->isApplicableOnCurrentPage()) {
          return $url;
        }
        break;
    }

    $config = $this->getConfig($key);
    if (!$config) {
      return $url;
    }

    $user_roles = $this->getUserRoles($account);
    $destination = $this->currentRequest->query->get('destination');
    foreach ($config as $role_type => $roles) {
      foreach ($roles as $role_id => $settings) {
        if (in_array($role_id, $user_roles[$role_type]) && $settings['redirect_url']) {
          // Prevent redirect if destination usage is allowed.
          if ($settings['allow_destination'] && $destination) {
            return $url;
          }

          if ($settings['replace_pattern']) {
            $program_id = $this->programService->getOrgId();
            $settings['redirect_url'] = str_replace('%group', $program_id, $settings['redirect_url']);
          }

          $url = Url::fromUserInput($settings['redirect_url']);
          return $url;
        }
      }
    }
  }

  /**
   * Return requested configuration items (login or logout) ordered by weight.
   *
   * @param string $key
   *   Configuration key (login or logout).
   *
   * @return array
   *   Requested configuration items (login or logout) ordered by weight.
   */
  protected function getConfig($key) {
    $config = $this->config->get($key);

    if ($config) {
      uasort($config, [SortArray::class, 'sortByWeightElement']);

      return $config;
    }

    return [];
  }

  /**
   * Return user roles list from given account or from current user.
   *
   * @param \Drupal\Core\Session\AccountInterface|null $account
   *   User account to get roles.
   *
   * @return array
   *   Roles list.
   */
  protected function getUserRoles(AccountInterface $account = NULL) {
    $roles = [];
    if ($account instanceof AccountInterface) {
      $user_roles = $account->getRoles();
    }
    else {
      $user_roles = $this->currentUser->getRoles();
    }
    $roles['system_roles'] = $user_roles;
    $roles += $this->getUserGroupRoles($account);

    return $roles;
  }

  /**
   * Get user roles from membership of groups.
   */
  protected function getUserGroupRoles(AccountInterface $account = NULL) {
    $groups = $group_roles = $user_programs = [];

    if ($account instanceof AccountInterface) {
      if (in_array('administrator', $account->getRoles())) {
        return $group_roles;
      }

      $program_id = \Drupal::service('opendevx_core.program_domain')->getProgramDomainId();
      try {
        $query = $this->connection->select('group_content_field_data', 'g');
        $query->addField('g', 'gid');
        $query->addField('gr', 'group_roles_target_id', 'role');
        $query->addField('gfd', 'type');
        $query->addJoin('inner', 'group_content__group_roles', 'gr', 'gr.entity_id = g.id');
        $query->addJoin('inner', 'groups_field_data', 'gfd', 'gfd.id = g.gid');
        $query->condition('g.type', ProgramInterface::MEMBERSHIP_TYPE, 'IN');
        $query->condition('g.entity_id', $account->id());
        if ($program_id) {
          $query->condition('g.gid', $program_id);
        }
        $query->OrderBy('gfd.type', "ASC");
        $groups = $query->execute()->fetchAll();
      }
      catch (\Exception $e) {
        $this->logger->log(
          ['module' => 'opendevx_login_redirect', 'message' => $e->getMessage()]
        );
      }

      foreach ($groups as $key => $group) {
        $group_roles[$group->type][$group->role] = $group->role;
        $user_programs[$group->gid] = $group->role;
        if ($key == 0) {
          $this->programService->setProgramId($group->gid);
          if (in_array($group->role, ProgramInterface::DEV_ROLES)) {
            $this->tempStore->set('store_pid', \Drupal::request()->get('pid'));
            $this->tempStore->set('store_path', \Drupal::request()->get('path'));
          }
        }
      }
      if ($program_id) {
        $this->programService->setProgramId($program_id);
      }

      $this->tempStore->set('user_programs', $user_programs);
    }

    return $group_roles;
  }

}
