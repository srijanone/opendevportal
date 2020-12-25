<?php

namespace Drupal\odp_navigation_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\odp_block\ApiProducts;
use Drupal\odp_block\Utility\BlockUtility;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;

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
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The Current Path Stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $path;

  /**
   * ProductHeaderNavigationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\odp_block\ApiProducts $product
   *   The products class service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The path alias manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ApiProducts $product,
   RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager, AliasManagerInterface $alias_manager,
   CurrentPathStack $current_path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->product = $product;
    $this->currentPath = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->aliasManager = $alias_manager;
    $this->path = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('odp_block.products'),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('path.alias_manager'),
      $container->get('path.current')
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
          if ($key == 'api_document') {
            $api_name = $list['api_document'];
          }
        }
      }
      $current_path = $this->currentPath->getCurrentRequest();
      if ($current_path->query->get('parent') != NULL) {
        $id = $current_path->query->get('parent');
      }
      else {
        $id = BlockUtility::getIdByPath($current_path);
      }
      $url_alias = $this->aliasManager->getAliasByPath('/node/' . $id);
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

      $url_alias = $this->path->getPath();
      $explode_path = explode('/', $url_alias);
      if ($current_path->query->get('parent') == NULL) {
        $id = $explode_path[2];
      }
      $node = $this->entityTypeManager->getStorage('node')->load($id);
      if ($node && $node->get('type')->getValue()[0]['target_id'] == 'api_product') {
        $api_path = $this->aliasManager->getAliasByPath('/node/' . $node->get('field_api_specifications')->getValue()[0]['target_id']) . '?parent=' . $id;
        $navigation_data[array_keys($navigation_data)[0]][$api_name]['childPath'] = $api_path;
      }
      $node_type = $this->entityTypeManager->getStorage('node')->load($explode_path[2]);
      if ($node_type && $explode_path[3] != 'document_overview') {
        if ($node_type->bundle() == 'api_product') {
          $navigation_data[array_keys($navigation_data)[0]][$list['api_product']]['childClass'] = 'active';
        }
        if ($node_type->bundle() == 'api_document') {
          $navigation_data[array_keys($navigation_data)[0]][$list['api_document']]['childClass'] = 'active';
        }
      }
      if ($explode_path[3] == 'document_overview') {
        $navigation_data[array_keys($navigation_data)[0]][$list['document_overview']]['childClass'] = 'active';
      }
    }
    return [
      '#theme' => 'product_header_navigation',
      '#navigationData' => array_values($navigation_data)[0],
      '#attached' => [
        'library' => [
          'odp_navigation_block/product_header_navigation',
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
