<?php

namespace Drupal\opendevx_navigation_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\opendevx_block\ApiProducts;
use Drupal\opendevx_block\Utility\BlockUtility;

/**
 * Provides a 'Product header Navigation' Block.
 *
 * @Block(
 *   id = "product_header_navigation_block",
 *   admin_label = @Translation("Product Header Navigation Block"),
 *   category = @Translation("Product Header Navigation Block"),
 * )
 */
class ProductHeaderNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Product detail.
   *
   * @var mixed
   */
  protected $product;

  /**
   * Current path.
   *
   * @var mixed
   */
  protected $currentPath;

  /**
   * ProductHeaderNavigationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\opendevx_block\ApiProducts $product
   *   The products class service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ApiProducts $product, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->product = $product;
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
      $container->get('opendevx_block.products'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['dvp_header_navigation'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Navigation Item'),
        $this->t('Change Name'),
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
    $listData = [
      'api_product' => $this->t('Product'),
      'api_document' => $this->t('API Reference'),
      'document_overview' => $this->t('Documentation'),
    ];
    $navigation = !empty($config['dvp_header_navigation']) ?
    array_combine(array_keys($config['dvp_header_navigation']), array_column($config['dvp_header_navigation'], 'type')) : $listData;
    foreach ($navigation as $key => $value) {
      $form['dvp_header_navigation'][$key]['#attributes']['class'][] = 'draggable';
      $form['dvp_header_navigation'][$key]['type'] = [
        '#size' => 30,
        '#type' => 'textfield',
        '#default_value' => $value,
        '#disabled' => TRUE,
      ];
      $form['dvp_header_navigation'][$key]['navigation_text'] = [
        '#size' => 30,
        '#type' => 'textfield',
        '#default_value' => !empty($config['dvp_header_navigation'][$key]['navigation_text']) ?
        $config['dvp_header_navigation'][$key]['navigation_text'] : '',
      ];
      $form['dvp_header_navigation'][$key]['show'] = [
        '#type' => 'select',
        '#options' => [1 => 'YES', 0 => 'NO'],
        '#default_value' => !empty($config['dvp_header_navigation'][$key]['show']) ? $config['dvp_header_navigation'][$key]['show'] : 1,
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
    $this->configuration['dvp_header_navigation'] = $values['dvp_header_navigation'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $navigation_data = "";
    if (!empty($this->configuration['dvp_header_navigation'])) {
      $navigation = $this->configuration['dvp_header_navigation'];
      $list = [];
      foreach ($navigation as $key => $value) {
        if ($value['show'] == 1) {
          $list[$key] = !empty($value['navigation_text']) ? $value['navigation_text'] : $value['type'];
        }
      }
      $current_path = $this->currentPath->getCurrentRequest();
      if ($current_path->query->get('parent') != NULL) {
        $id = $current_path->query->get('parent');
      }
      else {
        $id = BlockUtility::getIdByPath($current_path);
      }
      $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $id);
      $navigation_data = $this->product->setProductId($id)->getProductReferenceType($list);
      $product_pos = array_search('api_product', array_keys($list));
      $insert_value = [
        $list['api_product'] => [
          'childPath' => $url_alias,
          'childClass' => 'child_api_product',
        ],
      ];
      $navigation_data[array_keys($navigation_data)[0]] = $this->prePareValue(
        $navigation_data[array_keys($navigation_data)[0]],
        $product_pos,
        $insert_value
      );
    }

    return [
      '#theme' => 'product_header_navigation',
      '#navigationData' => array_values($navigation_data)[0],
      '#attached' => [
        'library' => [
          'opendevx_navigation_block/product_header_navigation',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * Create final data.
   */
  public function prePareValue($array, $position, $insert_array) {
    $first_array = array_splice($array, 0, $position);
    $array = array_merge($first_array, $insert_array, $array);

    return $array;
  }

}
