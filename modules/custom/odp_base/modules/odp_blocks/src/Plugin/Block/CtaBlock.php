<?php

namespace Drupal\odp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\odp_blocks\Utility\BlockUtility;

/**
 * Provides a 'CTA' Block.
 *
 * @Block(
 *   id = "cta_block",
 *   admin_label = @Translation("CTA Block"),
 *   category = @Translation("CTA Block"),
 * )
 */
class CtaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current path.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['cta_section'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Description'),
        $this->t('Type'),
        $this->t('URL Label'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'mytable-order-weight',
        ],
      ],
      '#prefix' => '<div id="main-container">',
      '#suffix' => '</div>',
    ];
    $iterate_cta = !empty($config['cta_section']) ? $config['cta_section'] : $config;
    foreach ($iterate_cta as $key => $value) {
      $form['cta_section'][$key]['#attributes']['class'][] = 'draggable';
      $form['cta_section'][$key]['cta_title'] = [
        '#size' => 50,
        '#type' => 'textfield',
        '#default_value' => !empty($config['cta_section'][$key]['cta_title']) ?
        $value['cta_title'] : '',
      ];
      $form['cta_section'][$key]['cta_description'] = [
        '#type' => 'textarea',
        '#rows' => 10,
        '#cols' => 50,
        '#resizable' => TRUE,
        '#default_value' => !empty($config['cta_section'][$key]['cta_description']) ?
        $value['cta_description'] : '',
      ];
      $form['cta_section'][$key]['cta_type'] = [
        '#type' => 'select',
        '#options' => ['apps' => $this->t('Create APP'), 'issues' => $this->t('Need Help')],
        '#default_value' => !empty($config['cta_section'][$key]['cta_type']) ?
        $value['cta_type'] : 'apps',
      ];
      $form['cta_section'][$key]['cta_label'] = [
        '#size' => 20,
        '#type' => 'textfield',
        '#default_value' => !empty($config['cta_section'][$key]['cta_label']) ?
        $value['cta_label'] : '',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['cta_section'] = $values['cta_section'];

  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_path = $this->currentPath->getCurrentRequest();
    $pid = BlockUtility::getIdByPath($current_path);
    $data = [];
    $i = 0;
    foreach ($this->configuration['cta_section'] as $value) {
      if (!empty($value['cta_title'])) {
        $data[$i]['ctaTitle'] = $value['cta_title'];
        $data[$i]['ctaDescription'] = $value['cta_description'];
        $data[$i]['ctaLabel'] = $value['cta_label'];
        $data[$i]['ctaUrl'] = '/cta/redirect-url/' . $value['cta_type'] . '/' . $pid;
      }
      $i++;
    }

    return [
      '#theme' => 'cta_section_block',
      '#ctaData' => $data,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
