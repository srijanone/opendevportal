<?php

namespace Drupal\odp_forum\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Build a block of Forum Stats.
 *
 * @Block(
 *   id = "Forum_stats_block",
 *   admin_label = @Translation("Forum Stats Block")
 * )
 */
class ForumStats extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ForumStats constructor.
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
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager) {
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
   * Build Forum Stats block.
   */
  public function build() {
    $posts = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'forum')
      ->execute();

    $posts = count($posts);

    $users = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('status', 1)
      ->execute();

    $users = count($users);

    $comments = $this->entityTypeManager->getStorage('comment')->getQuery()
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
