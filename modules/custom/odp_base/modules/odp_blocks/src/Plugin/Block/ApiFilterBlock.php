<?php

namespace Drupal\odp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'api filter' block.
 *
 * @Block(
 *   id = "api_filter_block",
 *   admin_label = @Translation("Api Filter block"),
 *   category = @Translation("Api Filter Block")
 * )
 */
class ApiFilterBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current path.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Form builder will be used via Dependency Injection.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * CtaBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RequestStack $request_stack,
    FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->request = $request_stack;
    $this->formBuilder = $form_builder;
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
      $container->get('request_stack'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (!empty($this->request->getCurrentRequest()->get('parent'))) {
      return $this->formBuilder->getForm('Drupal\odp_blocks\Form\ApiFilterForm');
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
