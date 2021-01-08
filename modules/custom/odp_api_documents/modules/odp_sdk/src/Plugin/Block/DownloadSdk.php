<?php

namespace Drupal\odp_sdk\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\odp_organisation\Utility\OrganisationUtility;
use Drupal\Core\Database\Connection;
use Drupal\odp_user\Logger\Logger;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Provides a 'Download SDK' Block.
 *
 * @Block(
 *   id = "download_sdk",
 *   admin_label = @Translation("Download SDK"),
 *   category = @Translation("Download SDK"),
 * )
 */
class DownloadSdk extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Opendevx Logger Service.
   *
   * @var \Drupal\odp_user\Logger\Logger
   */
  protected $logger;

  /**
   * Current path instance.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs a new DownloadSdkBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The databse service.
   * @param \Drupal\odp_user\Logger\Logger $logger
   *   The logger service.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path service.
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer,
    Connection $connection,
    Logger $logger,
    CurrentPathStack $current_path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->connection = $connection;
    $this->logger = $logger;
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('database'),
      $container->get('odp_user.logger'),
      $container->get('path.current')
    );
  }

  /**
   * Fetching API document.
   *
   * @return array
   *   API Document list.
   */
  public function getApiDocumentList($product_id) {
    try {
      $query = $this->connection->select('node_field_data', 'node_field_data');
      $query->addField('node_field_data', 'nid');
      $query->addField('api_specifications', 'field_api_specifications_target_id');
      $query->addJoin('left', 'node__field_api_specifications', 'api_specifications', 'node_field_data.nid = api_specifications.entity_id');
      $query->addJoin('left', 'node__field_api_type', 'api_type', 'api_specifications.field_api_specifications_target_id = api_type.entity_id');
      $query->condition('api_type.field_api_type_value', ['rest', 'async'], 'IN');
      $query->condition('node_field_data.status', 1);
      $query->condition('node_field_data.type', 'api_product');
      $query->condition('node_field_data.nid', $product_id);

      return $query->execute()->fetchAll();
    }
    catch (\Exception $e) {
      $this->logger->log(
        ['module' => 'odp_sdk', 'message' => $e->getMessage()]
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $path_index = explode('/', $this->currentPath->getPath());
    if (empty($path_index[2])) {
      return [
        'element_empty' => [
          '#markup' => '<div class="error">' . $this->t('You are trying to access invalid product item.') . '</div>',
        ],
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }

    $results = $this->getApiDocumentList($path_index[2]);
    $nids = [];
    foreach ($results as $result) {
      $nids[] = $result->field_api_specifications_target_id;
    }
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    foreach ($nodes as $node) {
      // @todo remove html markup and create template for block.
      $build[] = [
        'element_image' => [
          '#markup' => '<div class="product-image"><img src="' . ImageStyle::load('product_image')->buildUrl(
            OrganisationUtility::getImageUri($node->get('field_listing_image')->getValue()[0]['target_id'])
          ) . '"/></div>',
        ],
        'element_title' => [
          '#markup' => '<div class="title">' . $node->getTitle() . '</div>',
        ],
        'element_download' => [
          '#markup' => $this->renderer->render(\Drupal::formBuilder()->getForm('Drupal\odp_sdk\Form\DownloadSdk', $node->id())),
        ],
        '#cache' => [
          'max-age' => 0,
        ],
        '#prefix' => '<li class="card__item"><div class="card-paragraph">',
        '#suffix' => '</div></li>',
      ];
    }

    return $build;
  }

}
