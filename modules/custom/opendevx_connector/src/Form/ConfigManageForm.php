<?php

namespace Drupal\opendevx_connector\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage the configurations of the API's.
 */
class ConfigManageForm extends ConfigFormBase {

  /**
   * Config settings name for API management configurations.
   *
   * @var string
   */
  const CONFIGURATIONS_SETTINGS = 'opendevx_connector.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_config';
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
    $this->org_id = \Drupal::service('opendevx_user.organisation')->getOrgId();
    $form['api_platform'] = [
      '#type' => 'select',
      '#title' => $this->t('API Gateway Platform'),
      '#description' => $this->t('Select the API Gateway to use'),
      '#default_value' => $config->get('api_platform_options_' . $this->org_id),
      '#options' => [
        'none' => $this->t('-NONE-'),
        'apigee' => $this->t('APIGEE'),
        'webmethod' => $this->t('WEBMETHOD'),
        'wso2' => $this->t('WSO2'),
        'kong' => $this->t('KONG'),
      ],
      '#attributes' => [
        'name' => 'field_api_platform',
      ],
    ];
    $form['authentication'] = [
      '#type' => 'select',
      '#title' => $this->t('Authentication type'),
      '#description' => $this->t('Select the authentication method to use'),
      '#default_value' => $config->get('authentication_options_' . $this->org_id),
      '#options' => [
        'http' => $this->t('HTTP'),
        'oauth' => $this->t('Oauth'),
      ],
      '#states' => [
        //show this textfield only if the 'API Gateway Platform' is selected above
        'visible' => [
          ['select[name="field_api_platform"]' => ['value' => 'apigee']],
          ['select[name="field_api_platform"]' => ['value' => 'webmethod']],
          ['select[name="field_api_platform"]' => ['value' => 'wso2']],
          ['select[name="field_api_platform"]' => ['value' => 'kong']]
        ],
      ],
      '#attributes' => [
        'name' => 'field_authentication',
      ],
    ];
    $form['auth_server'] = [
      '#type' => 'textfield',
      '#title' => 'Authorization server',
      '#description' => $this->t('The authorization server endpoint'),
      '#default_value' => $config->get('auth_server_value_' . $this->org_id),
      '#states' => [
        //show this textfield only if the 'oauth' is selected above
        'visible' => [
          ':input[name="field_authentication"]' => ['value' => 'oauth'],
        ],
      ],
    ];
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => 'Client ID',
      '#description' => $this->t('The client identifier issued to the client'),
      '#default_value' => $config->get('client_id_' . $this->org_id),
      '#states' => [
        //show this textfield only if the 'oauth' is selected above
        'visible' => [
          ':input[name="field_authentication"]' => ['value' => 'oauth'],
        ],
      ],
    ];
    $form['client_secret'] = [
      '#type' => 'password',
      '#title' => 'Client secret',
      '#description' => $this->t('A secret known only to the client and the authorization server'),
      '#states' => [
        //show this password only if the 'oauth' is selected above
        'visible' => [
          ':input[name="field_authentication"]' => ['value' => 'oauth'],
        ],
      ],
    ];
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => 'Username',
      '#default_value' => $config->get('loggedin_usernames_' . $this->org_id),
      '#description' => $this->t('Add Platform username'),
      '#required' => TRUE,
      '#states' => [
        //show this textfield only if the 'API Gateway Platform' is selected above
        'visible' => [
          ['select[name="field_api_platform"]' => ['value' => 'apigee']],
          ['select[name="field_api_platform"]' => ['value' => 'webmethod']],
          ['select[name="field_api_platform"]' => ['value' => 'wso2']],
          ['select[name="field_api_platform"]' => ['value' => 'kong']]
        ],
      ],
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => 'Password',
      '#description' => $this->t('Add Platform password'),
      '#required' => TRUE,
      '#states' => [
        //show this textfield only if the 'API Gateway Platform' is selected above
        'visible' => [
          ['select[name="field_api_platform"]' => ['value' => 'apigee']],
          ['select[name="field_api_platform"]' => ['value' => 'webmethod']],
          ['select[name="field_api_platform"]' => ['value' => 'wso2']],
          ['select[name="field_api_platform"]' => ['value' => 'kong']]
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the username password.
    $this->config(self::CONFIGURATIONS_SETTINGS)
      ->set('api_platform_options_' . $this->org_id, $form_state->getUserInput()['field_api_platform'])
      ->set('authentication_options_' . $this->org_id, $form_state->getUserInput()['field_authentication'])
      ->set('auth_server_value_' . $this->org_id, $form_state->getValue('auth_server'))
      ->set('loggedin_usernames_' . $this->org_id, $form_state->getValue('username'))
      ->set('authentication_' . $this->org_id, $form_state->getValue('authentication'))
      ->set('client_id_' . $this->org_id, $form_state->getValue('client_id'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
