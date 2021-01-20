<?php

namespace Drupal\odp_sdk\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\odp_sdk\Utility\GenerateSdk;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a config form of Download SDK.
 */
class DownloadSdk extends FormBase {

  /**
   * Utility class object.
   *
   * @var \Drupal\odp_sdk\Utility\GenerateSdk
   */
  protected $sdk;

  /**
   * OpenDevPortal config Service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, GenerateSdk $generate_sdk) {
    $this->configFactory = $config_factory;
    $this->sdk = $generate_sdk;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('odp_sk.sdk_utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'odp_download_sdk';
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nid = NULL) {
    $form['sdk_languages'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose Language'),
      '#options' => $this->sdk->getSdkLanguages(),
    ];
    $form['sdk_nid'] = [
      '#type' => 'hidden',
      '#default_value' => $nid,
    ];
    $form['sdk_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Download'),
      '#attributes' => ['class' => ['link-wrapper']],
      '#submit' => [
        'callback' => [$this, 'sdkApiRequestHandler'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('sdk_languages') == '-none') {
      $form_state->setErrorByName('sdk_languages', $this->t('Choose a language to download a file!'));
    }
  }

  /**
   * The callback function to request to openapi-generator api.
   *
   * The file is downloaded on the ajax submit.
   */
  public function sdkApiRequestHandler(array $form, FormStateInterface $form_state) {
    $download_link = $this->sdk->sdkGenerateRequest(
      $this->configFactory->get('odp_sdk.settings')->get('generator_url'),
      $form_state->getValue('sdk_languages'),
      $this->sdk->getApiSpecFromNode($form_state->getValue('sdk_nid')),
    );

    if (isset($download_link)) {
      $form_state->setResponse(new TrustedRedirectResponse($download_link));
    }
  }

}
