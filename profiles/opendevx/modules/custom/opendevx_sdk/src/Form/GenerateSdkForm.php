<?php

namespace Drupal\opendevx_sdk\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opendevx_sdk\Utility\GenerateSdk;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Provides a config form of SDK Generation.
 */
class GenerateSdkForm extends FormBase {

  /**
   * Utility class object.
   *
   * @var object
   */
  protected $sdk;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->sdk = new GenerateSdk();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opendevx_generate_sdk';
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $sdk_languages = $this->sdk->getSdkLanguages();

    $form['sdk_languages'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose Language'),
      '#options' => $sdk_languages,
    ];
    // Create a ajax submit form element.
    $form['sdk_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate SDK'),
      '#ajax' => [
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
   * The callback function to request to openapi-generator api.
   *
   * The file is downloaded on the ajax submit.
   */
  public function sdkApiRequestHandler(array $form, FormStateInterface $form_state) {
    // Get the value of language selected for SDK generation.
    $formatter_option = $form_state->getValue('sdk_languages');
    // Get the url of the API Spec file.
    $apispec_path = $this->sdk->getApiSpecFromCurrentNode();
    // Get the IP of the openapi-generator container from the config settings.
    $apigenerator_url = \Drupal::config('opendevx_sdk.settings')->get('generator_url');
    // API request to generated the SDK for given language.
    $download_link = $this->sdk->sdkGenerateRequest($apigenerator_url, $formatter_option, $apispec_path);
    $response = new AjaxResponse();
    $command = new RedirectCommand($download_link);
    $response->addCommand($command);

    return $response;
  }

}
