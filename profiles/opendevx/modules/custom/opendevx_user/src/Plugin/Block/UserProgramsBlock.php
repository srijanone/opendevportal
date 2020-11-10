<?php

namespace Drupal\opendevx_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opendevx_organisation\Utility\OrganisationUtility;
use Drupal\opendevx_user\Organisation;

/**
 * Provides a 'User Profile Programs' Block.
 *
 * @Block(
 *   id = "user_programs_block",
 *   admin_label = @Translation("User Programs Block"),
 *   category = @Translation("User Programs Block"),
 * )
 */
class UserProgramsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $program;
  protected $currentPath;

   /**
   * UserProgramsBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param mixed $organisation
   *   The plugin organisation class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Organisation $organisation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->program = $organisation;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = $this->program->getUserProgramsData();
    return [
      '#theme' => 'user_programs',
      '#data' => $data,
      '#cache' => [
        'tags' => ['programs-section'],
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
