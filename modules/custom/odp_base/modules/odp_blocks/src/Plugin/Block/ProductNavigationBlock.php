<?php

namespace Drupal\odp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\odp_blocks\ApiProducts;
use Drupal\odp_blocks\Utility\BlockUtility;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'Product Navigation' Block.
 *
 * @Block(
 *   id = "product_navigation_block",
 *   admin_label = @Translation("Product Navigation Block"),
 *   category = @Translation("Product Navigation Block"),
 * )
 */
class ProductNavigationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The product object.
   *
   * @var mixed
   */
  protected $product;

  /**
   * The current path.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ProductNavigationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\odp_blocks\ApiProducts $product
   *   The products class service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ApiProducts $product,
    RequestStack $request_stack,
    AliasManagerInterface $alias_manager,
    EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->product = $product;
    $this->currentPath = $request_stack;
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('odp_blocks.products'),
      $container->get('request_stack'),
      $container->get('path.alias_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $content_list = BlockUtility::getBundleInfo();
    $contentTypesList = array_combine(array_keys($content_list),
    array_column($content_list, 'label'));
    // Remove content types from sidebar.
    unset($contentTypesList['api_product']);
    unset($contentTypesList['page']);
    unset($contentTypesList['listing_pages']);
    $form['dvp_sidebar_navigation'] = [
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
    $navigation = !empty($config['dvp_sidebar_navigation']) ?
    array_combine(array_keys($config['dvp_sidebar_navigation']), array_column($config['dvp_sidebar_navigation'], 'type')) : $contentTypesList;
    foreach ($navigation as $key => $value) {
      $form['dvp_sidebar_navigation'][$key]['#attributes']['class'][] = 'draggable';
      $form['dvp_sidebar_navigation'][$key]['type'] = [
        '#size' => 30,
        '#type' => 'textfield',
        '#default_value' => $value,
        '#disabled' => TRUE,
      ];
      $form['dvp_sidebar_navigation'][$key]['navigation_text'] = [
        '#size' => 30,
        '#type' => 'textfield',
        '#default_value' => !empty($config['dvp_sidebar_navigation'][$key]['navigation_text']) ?
        $config['dvp_sidebar_navigation'][$key]['navigation_text'] : '',
      ];
      $form['dvp_sidebar_navigation'][$key]['show'] = [
        '#type' => 'select',
        '#options' => [1 => 'YES', 0 => 'NO'],
        '#default_value' => !empty($config['dvp_sidebar_navigation'][$key]['show']) ? $config['dvp_sidebar_navigation'][$key]['show'] : 0,
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
    $this->configuration['dvp_sidebar_navigation'] = $values['dvp_sidebar_navigation'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $product_title = $navigation_data = "";
    if (!empty($this->configuration['dvp_sidebar_navigation'])) {
      $navigation = $this->configuration['dvp_sidebar_navigation'];
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
      $id = BlockUtility::getIdByPath($current_path);
      $navigation_data = $this->product->setProductId($id)->getProductReferenceType($list);
      if ($current_path->query->get('parent') == NULL) {
        $path = $current_path->getPathInfo();
        $url_alias = $this->aliasManager->getPathByAlias($path);
        $explode_path = explode('/', $url_alias);
        $id = $explode_path[2];
      }
      $node = $this->entityTypeManager->getStorage('node')->load($id);
      if ($node && $node->get('type')->getValue()[0]['target_id'] == 'api_product') {
        $api_path = '/node/' . $node->get('field_api_specifications')->getValue()[0]['target_id'] . '?parent=' . $id;
        $navigation_data[array_keys($navigation_data)[0]][$api_name]['childPath'] = $api_path;
      }
      $product_title = ucwords(str_replace("_", " ", array_keys($navigation_data)[0]));
    }
    return [
      '#theme' => 'product_navigation',
      '#navigationData' => array_values($navigation_data)[0],
      '#productTitle' => $product_title,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
