<?php

namespace Drupal\odp_subscription\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class SubscriptionSettings extends ConfigFormBase {

  /**
   * Set MODULE_KEY as configuration key.
   */
  const MODULE_KEY = "odp_subscription";

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;


  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The core messenger service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The module entity manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
  AccountProxyInterface $current_user,
  MessengerInterface $messenger,
  EntityTypeManagerInterface $entity_manager) {
    parent::__construct($config_factory);
    $this->currentUser = $current_user;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user'),
      $container->get('messenger'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::MODULE_KEY . '_mail_tempaltes';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::MODULE_KEY . '.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config(self::MODULE_KEY . '.settings');
    $form[self::MODULE_KEY . '_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Subscription settings'),
      '#collapsible' => TRUE,
    ];

    $form[self::MODULE_KEY . '_settings']['enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable newsletter mail notifications.'),
      '#default_value' => $config->get('enable'),
      '#description' => $this->t('Unchecking this node will not send any newsletter.'),
    ];

    $form[self::MODULE_KEY . '_settings']['batch_limit'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Batch Limit.'),
      '#default_value' => $config->get('batch_limit'),
      '#description' => $this->t('Limit records process per cron run.'),
    ];

    $form[self::MODULE_KEY . '_settings']['log_messages'] = [
      '#type' => 'radios',
      '#title' => $this->t('Watchdog Log'),
      '#default_value' => $config->get('log_messages'),
      '#options' => [$this->t('All'), $this->t('Nothing')],
      '#description' => $this->t('This setting lets you specify to log messages.'),
    ];

    // Get all node bundles.
    $all_content_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $node_bundles = [];
    foreach ($all_content_types as $machine_name => $content_type) {
      $node_bundles[$machine_name] = $content_type->label();
    }
    $default_value = $config->get('entity_types');

    $form[self::MODULE_KEY . '_settings']['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Notify on'),
      '#default_value' => $default_value,
      '#options' => $node_bundles,
      '#description' => $this->t('This setting lets you choose the entity types on which the notification will trigger.'),
      '#prefix' => '<div id="entity-check">',
      '#suffix' => '</div>',
    ];

    $form[self::MODULE_KEY . '_settings']['include_updates'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Notify on update.'),
      '#return_value' => 1,
      '#default_value' => $config->get('include_updates'),
      '#description' => $this->t('This setting lets you specify if notification will also trigger on updates.'),
    ];

    $form[self::MODULE_KEY . '_mail_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('E-mail notification settings'),
      '#collapsible' => TRUE,
    ];

    $form[self::MODULE_KEY . '_mail_settings']['include_unpublished'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Administrators shall be notified about unpublished content'),
      '#return_value' => 1,
      '#default_value' => $config->get('include_unpublished'),
    ];

    $form[self::MODULE_KEY . '_mail_settings']['mail_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mail Subject'),
      '#default_value' => $config->get('mail_subject'),
    ];

    $form[self::MODULE_KEY . '_mail_settings']['mail_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Mail Body'),
      '#default_value' => $config->get('mail_body'),
    ];
    if (\Drupal::service('module_handler')->moduleExists('token')) {
      // Add the token tree UI.
      $form[self::MODULE_KEY . '_mail_settings']['token_tree'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['user'],
        '#show_restricted' => TRUE,
        '#weight' => 90,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $batch_limit = $form_state->getValue('batch_limit');
    if (!is_numeric($batch_limit)) {
      return $form_state->setErrorByName('batch_limit', $this->t('Batch Limit must be a valid integer.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config(self::MODULE_KEY . '.settings')
      ->set('enable', $values['enable'])
      ->set('batch_limit', $values['batch_limit'])
      ->set('log_messages', $values['log_messages'])
      ->set('entity_types', $values['entity_types'])
      ->set('include_updates', $values['include_updates'])
      ->set('include_unpublished', $values['include_unpublished'])
      ->set('mail_subject', $values['mail_subject'])
      ->set('mail_body', $values['mail_body'])
      ->save();

    $this->messenger->addMessage($this->t('Notify admin settings saved.'));
  }

}
