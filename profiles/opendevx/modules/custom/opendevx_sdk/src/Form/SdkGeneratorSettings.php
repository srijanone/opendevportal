<?php

namespace Drupal\opendevx_sdk\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SDK Generator Settings Form.
 */
class SdkGeneratorSettings extends ConfigFormBase {

  /**
   * Set MODULE_KEY as configuration key.
   */
  const MODULE_KEY = "opendevx_sdk";

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The core messenger service.
   */
  public function __construct(ConfigFactoryInterface $config_factory,
  MessengerInterface $messenger) {
    parent::__construct($config_factory);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::MODULE_KEY . '_config';
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
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config(self::MODULE_KEY . '.settings');
    $form[self::MODULE_KEY . '_settings'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
    ];

    $form[self::MODULE_KEY . '_settings']['generator_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Generator Base URL.'),
      '#default_value' => $config->get('generator_url'),
      '#description' => $this->t('This will be private IP.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config(self::MODULE_KEY . '.settings')
      ->set('generator_url', $values['generator_url'])
      ->save();

    $this->messenger->addMessage($this->t('SDK generator settings saved successfully.'));
  }

}
