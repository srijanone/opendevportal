<?php

namespace Drupal\odp_taxonomy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage the general configurations.
 */
class ApiDefaultValueConfigForm extends ConfigFormBase {

  /**
   * Config settings name.
   *
   * @var string
   */
  const CONFIGURATIONS_SETTINGS = 'odp_taxonomy.api_default_values';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'apidefault_value_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::CONFIGURATIONS_SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIGURATIONS_SETTINGS);
    $form['api_environment'] = [
      '#type' => 'select',
      '#title' => $this->t('API Environment Default Value'),
      '#options' => $this->getReferenceFieldOptions('environment'),
      '#default_value' => $config->get('api_environment'),
    ];
    $form['api_version'] = [
      '#type' => 'select',
      '#title' => $this->t('API Version Default Value'),
      '#options' => $this->getReferenceFieldOptions('version'),
      '#default_value' => $config->get('api_version'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the API environment and API version values.
    $this->config(self::CONFIGURATIONS_SETTINGS)
      ->set('api_environment', $form_state->getValue('api_environment'))
      ->set('api_version', $form_state->getValue('api_version'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceFieldOptions($vid) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }

    return $term_data;
  }

}
