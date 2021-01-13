<?php

namespace Drupal\odp_program\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\odp_program\Program;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'Front Program' Block.
 *
 * @Block(
 *   id = "front_program_block",
 *   admin_label = @Translation("Front Program Block"),
 *   category = @Translation("Front Program Block"),
 * )
 */
class FrontProgramBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $program;
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
   * @param mixed $program
   *   The plugin program class.
   * @param mixed $request_stack
   *   The plugin request stack service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Program $program,
    RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->program = $program;
    $this->currentPath = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('odp_program.program'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $data = $this->program->getProgramData();
    return [
      '#theme' => 'front_program',
      '#data' => $data,
      '#cache' => [
        'tags' => ['Program-section'],
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
