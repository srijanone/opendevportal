<?php

namespace Drupal\opendevx_sdk\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Generate SDK' Block.
 *
 * @Block(
 *   id = "generate_sdk",
 *   admin_label = @Translation("Generate SDK"),
 *   category = @Translation("Generate SDK"),
 * )
 */
class GenerateSdkBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('Drupal\opendevx_sdk\Form\GenerateSdkForm');
  }

}
