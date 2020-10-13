<?php

namespace Drupal\opendevx_user\Plugin\views\argument_default;

use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opendevx_user\Organisation as UserOrganisations;
use Drupal\opendevx_organisation\Organisation;
use Drupal\Core\Session\AccountInterface;

/**
 * Default argument plugin to extract the current user organisation.
 *
 * This plugin actually has no options so it does not need to do a great deal.
 *
 * @ViewsArgumentDefault(
 *   id = "current_user_organisation",
 *   title = @Translation("Program ID from logged in user")
 * )
 */
class UserOrganisation extends ArgumentDefaultPluginBase {

  /**
   * UserOrganisations object.
   *
   * @var \Drupal\opendevx_user\UserOrganisations $userOrg
   *
   */
  protected $userOrg;

  /**
   * Organisation object.
   *
   * @var \Drupal\opendevx_organisation\Organisation $org
   *
   */
  protected $org;

  /**
   * Organisation object.
   *
   * @var \Drupal\opendevx_organisation\Organisation $account
   *
   */
  protected $account;

   /**
   * UserOrganisationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param mixed $user_organisation
   *   The plugin user organisation class.
   * @param mixed $organisation
   *   The plugin organisation class.
   * @param mixed $account
   *   The plugin AccountInterface class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, UserOrganisations $user_organisation,
  Organisation $organisation, AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userOrg = $user_organisation;
    $this->org = $organisation;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('opendevx_user.organisation'),
      $container->get('opendevx_organisation.organisation'),
      $container->get('current_user'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    if (!in_array('administrator', $this->account->getRoles()) && $this->userOrg->getOrgId() > 0) {
      return $this->userOrg->getOrgId();
    }
    else {
      return implode("+", array_keys($this->org->getOrganisationsData()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
