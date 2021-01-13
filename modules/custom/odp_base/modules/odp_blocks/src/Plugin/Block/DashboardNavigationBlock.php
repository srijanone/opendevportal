<?php

namespace Drupal\odp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\odp_blocks\Utility\BlockUtility;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a 'Dashboard Navigation' Block.
 *
 * @Block(
 *   id = "dashboard_navigation_block",
 *   admin_label = @Translation("Dashboard Navigation Block"),
 *   category = @Translation("Dashboard Navigation Block"),
 * )
 */
class DashboardNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current path.
   *
   * @var mixed
   */
  protected $currentPath;

  /**
   * DashboardNavigationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack) {
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
    $contentTypesList = BlockUtility::getBundleInfo(['description']);
    unset($contentTypesList['api_product']);
    unset($contentTypesList['api_document']);
    unset($contentTypesList['page']);
    unset($contentTypesList['listing_pages']);
    $form['dvp_product_sidebar_navigation'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Navigation Item'),
        $this->t('Change Name'),
        $this->t('Description'),
        $this->t('Show'),
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
    $navigation = !empty($config['dvp_product_sidebar_navigation']) ? $config['dvp_product_sidebar_navigation'] : $contentTypesList;
    foreach ($navigation as $key => $value) {
      $form['dvp_product_sidebar_navigation'][$key]['#attributes']['class'][] = 'draggable';
      $form['dvp_product_sidebar_navigation'][$key]['label'] = [
        '#size' => 30,
        '#type' => 'textfield',
        '#default_value' => $value['label'],
        '#disabled' => TRUE,
      ];
      $form['dvp_product_sidebar_navigation'][$key]['navigation_text'] = [
        '#size' => 30,
        '#type' => 'textfield',
        '#default_value' => !empty($config['dvp_product_sidebar_navigation'][$key]['navigation_text']) ?
        $config['dvp_product_sidebar_navigation'][$key]['navigation_text'] : '',
      ];
      $form['dvp_product_sidebar_navigation'][$key]['navigation_description'] = [
        '#size' => 75,
        '#type' => 'textfield',
        '#default_value' => !empty($config['dvp_product_sidebar_navigation'][$key]['navigation_description']) ?
        $config['dvp_product_sidebar_navigation'][$key]['navigation_description'] : '',
      ];
      $form['dvp_product_sidebar_navigation'][$key]['show'] = [
        '#type' => 'select',
        '#options' => [1 => 'YES', 0 => 'NO'],
        '#default_value' => !empty($config['dvp_product_sidebar_navigation'][$key]['show']) ? $config['dvp_product_sidebar_navigation'][$key]['show'] : 0,
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
    $this->configuration['dvp_product_sidebar_navigation'] = $values['dvp_product_sidebar_navigation'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $result = [];
    // Fetch the product ID from the path.
    $path = $this->currentPath->getCurrentRequest();
    $product_id = BlockUtility::getIdByPath($path, 'dashboard_navigation');
    if (!empty($product_id)) {
      // Prepare the list of content types in system.
      if (!empty($this->configuration['dvp_product_sidebar_navigation'])) {
        $navigation = $this->configuration['dvp_product_sidebar_navigation'];
        foreach ($navigation as $key => $value) {
          if ($value['show'] == 1) {
            $bundles[$key]['label'] = $value['navigation_text'];
            $bundles[$key]['description'] = $value['navigation_description'];
          }
        }
      }
      else {
        $bundles = BlockUtility::getBundleInfo(['description']);
        unset($bundles['api_product']);
        unset($bundles['api_document']);
        unset($bundles['page']);
        unset($bundles['listing_pages']);
      }
      // Prepare the list of menu and navigation for the dashboard.
      if (!empty($bundles)) {
        $result = BlockUtility::prepareDashboardNavBlock($bundles, $product_id);

        return [
          '#theme' => 'dashboard_product_navigation',
          '#navigationData' => array_values($result)[0],
          '#productTitle' => ucwords(str_replace("_", " ", array_keys($result)[0])),
        ];
      }
    }

    return [
      '#theme' => 'dashboard_product_navigation',
      '#navigationData' => NULL,
      '#productTitle' => $this->t('There is no navigation item is added.'),
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
