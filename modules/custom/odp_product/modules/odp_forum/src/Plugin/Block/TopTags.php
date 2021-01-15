<?php

namespace Drupal\odp_forum\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Build a block of Top Tags.
 *
 * @Block(
 *   id = "Top_Tags_block",
 *   admin_label = @Translation("Top Tags Block")
 * )
 */
class TopTags extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * TopTags constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Build Top Tags Block.
   */
  public function build() {
    $forum_ids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'forum')
      ->execute();
    if ($forum_ids) {
      $forum_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($forum_ids);
      $tag_names = [];
      foreach ($forum_nodes as $forum_node) {
        if ($forum_node->hasField('field_tags') && $forum_node->field_tags->entity) {
          $tags = $forum_node->field_tags->referencedEntities();
          foreach ($tags as $tag) {
            $tag_name[] = $tag->getName();
          }
          $tag_names = array_merge($tag_names, $tag_name);
        }
      }
      $value = array_count_values($tag_names);
      arsort($value);
      $top_tags = array_keys($value);
    }

    return [
      '#theme' => 'top_tags',
      '#tags' => $top_tags,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
