<?php

namespace Drupal\opendevx_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\opendevx_organisation\Utility\OrganisationUtility;
use Drupal\opendevx_user\Organisation as UserOrganisation;
use Drupal\opendevx_organisation\Organisation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\opendevx_block\Utility\BlockUtility;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'Product Banner' Block.
 *
 * @Block(
 *   id = "organisation_site_logo",
 *   admin_label = @Translation("Organisation Site Logo"),
 *   category = @Translation("Organisation Site Logo"),
 * )
 */
class OrganisationSiteLogo extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * UserOrganisation object.
   *
   * @var \Drupal\opendevx_user\UserOrganisation $userOrg
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
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ProductBannerBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param mixed $request_stack
   *   The plugin request stack service.
   * @param mixed $entity_type_manager
   *   The EntityTypeManagerInterface.
   * @param mixed $user_organisation
   *   The plugin user organisation service.
   * @param mixed $organisation
   *   The plugin organisation service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
  RequestStack $request_stack,
  EntityTypeManagerInterface $entity_type_manager,
  UserOrganisation $user_organisation,
  organisation $organisation,
  AccountInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentPath = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->userOrg = $user_organisation;
    $this->org = $organisation;
    $this->account = $account;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container,
  array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('opendevx_user.organisation'),
      $container->get('opendevx_organisation.organisation'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $organisation_logo = $organisation_title = '';
    $current_path = $this->currentPath->getCurrentRequest();
    if (!empty($this->userOrg->getOrgId())) {
      // Get organisation id from temp storage.
      $org_id = $this->userOrg->getOrgId();
    }
    $id = BlockUtility::getIdByPath($current_path);
    if (!empty($id)) {
      $node = $this->entityTypeManager->getStorage('node')->load($id);
      $parent = $current_path->get('parent');
      // Check if user is anonymous.
      if ($this->account->isAnonymous() == TRUE) {
        // Check for the organisation landing pages or parent query string 
        //if ($node->bundle() == 'listing_pages' || !empty($current_path->get('parent'))) {
          $node_data =  $node->toArray();
          $org_id = (int) $node_data['field_organisation'][0]['target_id'];
       // }
      }
    }
    $org_data = $this->org->getOrganisationsData();
    $organisation_logo = $org_data[$org_id]['orgImage'];
    $organisation_title = $org_data[$org_id]['orgTitle'];
   
    return [
      '#theme' => 'organisation_site_logo',
      '#orgLogo' => $organisation_logo,
      '#orgTitle' => $organisation_title,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
