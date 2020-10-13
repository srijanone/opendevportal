<?php

namespace Drupal\opendevx_connector\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage the general configurations.
 */
class GatewayHubConfigForm extends ConfigFormBase {

  /**
   * Config settings name.
   *
   * @var string
   */
  const CONFIGURATIONS_SETTINGS = 'opendevx_connector.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gatewayhub_config_form';
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
    $form['gateway_hub'] = [
      '#type' => 'details',
      '#title' => $this->t('Gateway Hub config'),
      '#open' => TRUE,
    ];
    $form['gateway_hub']['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#description' => $this->t('Gateway hub url without "/" at the end, for updating gateway and group
      content on event. eg: http://www.example.com'),
      '#default_value' => $config->get('url'),
    ];
    $form['gateway_hub']['portal_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Portal ID'),
      '#description' => $this->t('Portal id'),
      '#default_value' => $config->get('portal_id'),
    ];
    $form['gateway_hub']['cred'] = [
      '#type' => 'password',
      '#title' => $this->t('Gateway hub credentials'),
      '#description' => $this->t('Place credentials in format like username:password'),
      '#default_value' => $config->get('cred'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the username password.
    $this->config(self::CONFIGURATIONS_SETTINGS)
      ->set('url', $form_state->getValue('url'))
      ->set('portal_id', $form_state->getValue('portal_id'))
      ->set('cred', base64_encode($form_state->getValue('cred')))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
