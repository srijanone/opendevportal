<?php

namespace Drupal\odp_notification\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a config form of notification.
 */
class OpendevxNotificationForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an AutologoutSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The module entity manager service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['odp_notification.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'odp_notification_settings';
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('odp_notification.settings');
    $form['workflow_type'] = [
      '#type' => 'fieldset',
      '#title' => 'Workflow Types',
    ];
    $workflow = [
      'api_approval_workflow',
      'approval_workflow',
    ];
    foreach ($workflow as $value) {
      $form['workflow_type'] += $this->prepareWorkflowTypeForm(
        $value,
        $config,
        'Workflow'
      );
    }
    // Content Type list.
    $nodeTypeStorage = $this->entityTypeManager->getStorage('node_type');
    $nodeTypes       = $nodeTypeStorage->loadMultiple();

    $form['content_type'] = [
      '#type' => 'details',
      '#title' => 'Content Types',
      '#open' => FALSE,
    ];
    foreach ($nodeTypes as $nodeType => $nodeTypeData) {
      $nodeTypeName = $nodeTypeData->get('name');
      $option['workflow_' . $nodeType] = $nodeTypeName;
    }
    $form['content_type']['workflow_type'] = [
      '#type' => 'checkboxes',
      '#title' => 'Enable Notification for Content type',
      '#options' => $option,
      '#default_value' => $config->get('workflow_type'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function prepareWorkflowTypeForm($workflow, $config, $entityName) {
    $data = $this->config('workflows.workflow.' . $workflow);
    $label = $data->get('label');
    $settings = $data->get('type_settings');
    $states = $settings['states'];
    $node = $settings['entity_types']['node'];
    $form = [];
    // Set the workflow name as per node type.
    if (empty($node)) {
      return $form;
    }
    foreach ($node as $val) {
      $form[$val] = [
        '#type' => 'hidden',
        '#value' => $workflow,
      ];
    }

    $form[$workflow] = [
      '#type'  => 'details',
      '#title' => $this->t('@entityName for - @workflowName',
        [
          '@entityName'   => $entityName,
          '@workflowName' => $label,
        ]
      ),
      '#open'  => FALSE,
    ];
    $notification = $this->config('odp_notification.settings');
    foreach ($states as $key => $transition) {
      $title = $transition['label'];
      $message_key = $workflow . '_' . $key;
      $messageValue = empty($notification->get($message_key)) ?
        '[node:title] has been ' . $title : $notification->get($message_key);
      $redirectValue = empty($notification->get($message_key)) ?
       '[entity:url]' : $notification->get($key . '_redirect_link');
      $form[$workflow][$message_key] = [
        '#type'          => 'textfield',
        '#title'         => $title . ' Notification Message',
        '#default_value' => $messageValue,
      ];
      $form[$workflow][$key . '_redirect_link'] = [
        '#type'          => 'textfield',
        '#title'         => $title . ' Notification Message URL',
        '#description'   => $this->t('The url, path for this notification to link. Leave blank if no link is required.'),
        '#default_value' => $redirectValue,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();
    unset($values['submit']);
    $config = $this->config('odp_notification.settings');
    foreach ($values as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

}
