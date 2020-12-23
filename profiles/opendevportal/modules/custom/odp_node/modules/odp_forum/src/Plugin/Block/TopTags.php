<?php

namespace Drupal\odp_forum\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Build a block of Top Tags.
 *
 * @Block(
 *   id = "Top_Tags_block",
 *   admin_label = @Translation("Top Tags Block")
 * )
 */
class TopTags extends BlockBase {

  /**
   * Build Top Tags Block.
   */
  public function build() {
    $forum_ids = \Drupal::entityQuery('node')
      ->condition('type', 'forum')
      ->execute();
    if ($forum_ids) {
      $forum_nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($forum_ids);
      $tag_names = [];
      foreach ($forum_nodes as $key => $forum_node) {
        if ($forum_node->hasField('field_tags') && $forum_node->field_tags->entity) {
          $tags = $forum_node->field_tags->referencedEntities();
          foreach ($tags as $key => $tag) {
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
