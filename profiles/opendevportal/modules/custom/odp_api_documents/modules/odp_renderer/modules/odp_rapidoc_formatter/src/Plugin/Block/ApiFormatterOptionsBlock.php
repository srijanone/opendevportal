<?php

namespace Drupal\odp_rapidoc_formatter\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'API Formatter Options' Block.
 *
 * @Block(
 *   id = "api_formatter_option_block",
 *   admin_label = @Translation("API Formatter Option Block"),
 *   category = @Translation("API Formatter Block"),
 * )
 */
class ApiFormatterOptionsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\odp_rapidoc_formatter\Form\ApiFormatterOptionsForm');
  }

}
