<?php

namespace Drupal\developer_portal_connector\Form;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manage the configurations of the program keys.
 */
class ConfigPlatformKeysForm extends ConfigFormBase {

  /**
   * Config settings name for API management configurations.
   *
   * @var string
   */
  const CONFIGURATIONS_SETTINGS = 'developer_portal_connector.program_keys.settings';

  /**
   * @var int
   *  Private member variable $orgId.
   */
  private $orgId;

  /**
   * @var string
   *  Private member variable $orgName.
   */
  private $orgName;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_platform_keys';
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
  public function buildForm(array $form, FormStateInterface $form_state, $program = NULL) {
    // Load configurations.
    $config = $this->config(self::CONFIGURATIONS_SETTINGS);
    $current_user_role = \Drupal::currentUser()->getRoles(TRUE);
    // Get user's all organisations.
    $user_organisations = \Drupal::service('opendevx_user.organisation')->getUserOrganisations();
    // Get organization's admin roles.
    $admin_roles = \Drupal::service('opendevx_user.organisation')->getAdminRoles(TRUE);

    // Return if not a valid program.
    if ($program->getVocabularyId() != 'organisation' ||
    (!array_intersect($current_user_role, $admin_roles)
    && !in_array($program->id(), array_column($user_organisations, 'orgId')))) {
      throw new NotFoundHttpException();
    }
    // If everything goes well, set current program id as orgId.
    $this->orgId = $program->id();
    $this->orgName = $program->getName();
    $form['program'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Program'),
      '#default_value' => $config->get('program_' . $this->orgName),
      '#access' => FALSE,
    ];
    $form['api_platform'] = [
      '#type' => 'select',
      '#title' => $this->t('API Gateway Platform'),
      '#description' => $this->t('Select the API Gateway to use'),
      '#default_value' => $config->get('api_platform_options_' . $this->orgId),
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
      '#default_value' => $config->get('authentication_options_' . $this->orgId),
      '#options' => [
        'http' => $this->t('HTTP'),
        'oauth' => $this->t('Oauth'),
      ],
      '#states' => [
        // Show this textfield only if the
        // 'API Gateway Platform' is selected above.
        'visible' => [
          ['select[name="field_api_platform"]' => ['value' => 'apigee']],
          ['select[name="field_api_platform"]' => ['value' => 'webmethod']],
          ['select[name="field_api_platform"]' => ['value' => 'wso2']],
          ['select[name="field_api_platform"]' => ['value' => 'kong']],
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
      '#default_value' => $config->get('auth_server_value_' . $this->orgId),
      '#states' => [
        // Show this textfield only if the 'oauth' is selected above.
        'visible' => [
          ':input[name="field_authentication"]' => ['value' => 'oauth'],
        ],
      ],
    ];
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => 'Client ID',
      '#description' => $this->t('The client identifier issued to the client'),
      '#default_value' => $config->get('client_id_' . $this->orgId),
      '#states' => [
        // Show this textfield only if the 'oauth' is selected above.
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
        // Show this password only if the 'oauth' is selected above.
        'visible' => [
          ':input[name="field_authentication"]' => ['value' => 'oauth'],
        ],
      ],
    ];
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => 'Username',
      '#default_value' => $config->get('loggedin_usernames_' . $this->orgId),
      '#description' => $this->t('Add Platform username'),
      '#required' => TRUE,
      '#states' => [
        // Show this textfield only if the
        // 'API Gateway Platform' is selected above.
        'visible' => [
          ['select[name="field_api_platform"]' => ['value' => 'apigee']],
          ['select[name="field_api_platform"]' => ['value' => 'webmethod']],
          ['select[name="field_api_platform"]' => ['value' => 'wso2']],
          ['select[name="field_api_platform"]' => ['value' => 'kong']],
        ],
      ],
    ];
    $form['password'] = [
      '#type' => 'password',
      '#title' => 'Password',
      '#description' => $this->t('Add Platform password'),
      '#required' => TRUE,
      '#states' => [
        // Show this textfield only if the
        // 'API Gateway Platform' is selected above.
        'visible' => [
          ['select[name="field_api_platform"]' => ['value' => 'apigee']],
          ['select[name="field_api_platform"]' => ['value' => 'webmethod']],
          ['select[name="field_api_platform"]' => ['value' => 'wso2']],
          ['select[name="field_api_platform"]' => ['value' => 'kong']],
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
      ->set('program_' . $this->orgId, $this->orgName)
      ->set('api_platform_options_' . $this->orgId, $form_state->getUserInput()['field_api_platform'])
      ->set('authentication_options_' . $this->orgId, $form_state->getUserInput()['field_authentication'])
      ->set('auth_server_value_' . $this->orgId, $form_state->getValue('auth_server'))
      ->set('loggedin_usernames_' . $this->orgId, $form_state->getValue('username'))
      ->set('authentication_' . $this->orgId, $form_state->getValue('authentication'))
      ->set('client_id_' . $this->orgId, $form_state->getValue('client_id'))
      ->save();
    parent::submitForm($form, $form_state);
    $this->messenger()->addStatus($this->t('The configurations have saved successfully.', [
      '%ip' => $this->banIp,
    ]));
    // Clear views cache.
    drupal_flush_all_caches();

  }

}
