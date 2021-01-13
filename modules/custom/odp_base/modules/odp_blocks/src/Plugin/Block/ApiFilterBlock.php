<?php

namespace Drupal\odp_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'api filter' block.
 *
 * @Block(
 *   id = "api_filter_block",
 *   admin_label = @Translation("Api Filter block"),
 *   category = @Translation("Api Filter Block")
 * )
 */
class ApiFilterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\odp_blocks\Form\ApiFilterForm');
    if (!empty(\Drupal::request()->query->get('parent'))) {
      return $form;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
