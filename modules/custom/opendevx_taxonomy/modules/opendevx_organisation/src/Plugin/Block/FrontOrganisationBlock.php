<?php

namespace Drupal\opendevx_organisation\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opendevx_organisation\Organisation;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'Front Organisation' Block.
 *
 * @Block(
 *   id = "front_organisation_block",
 *   admin_label = @Translation("Front Organisation Block"),
 *   category = @Translation("Front Organisation Block"),
 * )
 */
class FrontOrganisationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Organisation instance.
   *
   * @var \Drupal\opendevx_organisation\Organisation
   */
  protected $org;

  /**
   * The request stack instance.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * FrontOrganisationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\opendevx_organisation\Organisation $organisation
   *   The plugin organisation class.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Organisation $organisation, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->org = $organisation;
    $this->currentPath = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('opendevx_organisation.organisation'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = $this->org->getOrganisationsDataByAccess();
    return [
      '#theme' => 'front_organisation',
      '#data' => $data,
      '#cache' => [
        'tags' => ['Organisation-section'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
