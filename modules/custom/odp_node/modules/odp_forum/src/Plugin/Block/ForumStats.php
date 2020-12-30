<?php

namespace Drupal\odp_forum\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Build a block of Forum Stats.
 *
 * @Block(
 *   id = "Forum_stats_block",
 *   admin_label = @Translation("Forum Stats Block")
 * )
 */
class ForumStats extends BlockBase {

  /**
   * Build Forum Stats block.
   */
  public function build() {
    $posts = \Drupal::entityQuery('node')
      ->condition('type', 'forum')
      ->execute();

    $posts = count($posts);

    $users = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->execute();

    $users = count($users);

    $comments = \Drupal::entityQuery('comment')
      ->condition('comment_type', 'comment_forum')
      ->execute();

    $comments = count($comments);
    return [
      '#theme' => 'forum_stats',
      '#posts' => $posts,
      '#users' => $users,
      '#comments' => $comments,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
