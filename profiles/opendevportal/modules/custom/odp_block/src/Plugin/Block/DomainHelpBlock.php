<?php

namespace Drupal\odp_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Domain configuration' Help Block.
 *
 * @Block(
 *   id = "domain_help_block",
 *   admin_label = @Translation("Domain configuration help Block"),
 *   category = @Translation("Domain configuration help Block"),
 * )
 */
class DomainHelpBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['domain_help_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Help Text'),
      '#default_value' => ($config['domain_help_text']['value']) ?: '',
      '#format' => 'full_html',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['domain_help_text'] = $values['domain_help_text'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->configuration['domain_help_text']['value'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
