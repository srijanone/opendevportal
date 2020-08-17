<?php

namespace Drupal\developer_portal_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\developer_portal_block\Utility\BlockUtility;
use Drupal\opendevx_paragraph\Paragraph;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'Product Banner' Block.
 *
 * @Block(
 *   id = "product_banner_block",
 *   admin_label = @Translation("Product Banner Block"),
 *   category = @Translation("Product Banner Block"),
 * )
 */
class ProductBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var mixed $paragraph
   */
  protected $paragraph;

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

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
   * @param mixed $entity_type_manager
   *   The EntityTypeManagerInterface.
   * @param mixed $request_stack
   *   The plugin request stack service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
  RequestStack $request_stack,
  EntityTypeManagerInterface $entity_type_manager,
  Paragraph $paragraph) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentPath = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->paragraph = $paragraph;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('opendevx_paragraph.paragraph')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $title = $description = $banner_img = '';
    $current_path = $this->currentPath->getCurrentRequest();
    $id = BlockUtility::getIdByPath($current_path);
    if (!empty($id)) {
      $node = $this->entityTypeManager->getStorage('node')->load($id);
      $node_data = isset($node) ? $node->toArray() : [];
      if ($node_data['field_products_image']) {
        $banner = array_column($node_data['field_products_image'], 'target_id');
      }
      $key_value = [];
      if (!empty($node_data['field_product_attributes'])) {
        $product_attributes = array_map('intval',
        array_column($node_data['field_product_attributes'], 'target_id'));
        $key_value =
        $this->paragraph->setParagraphId($product_attributes)->getProductAttributesData();
      }

      if (!empty($banner)) {
        $banner_img = BlockUtility::generateMediaImage((int) $banner[0], 'banner');
      }
      $title = $node->getTitle();
      $description = $node_data['body'][0]['value'];
      $max_description_length = 130;
      if (strlen($description) > $max_description_length) {
        $offset = ($max_description_length - 3) - strlen($description);
        $description = substr($description, 0, strrpos($description, ' ', $offset)) . '...';
      }
    }

    // TODO: Correct the cache ID.
    return [
      '#theme' => 'product_banner',
      '#banner' => $banner_img,
      '#title' => $title,
      '#description' => $description,
      '#productAttributes' => $key_value,
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }

}
