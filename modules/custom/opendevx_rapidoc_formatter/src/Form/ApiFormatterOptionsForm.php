<?php

namespace Drupal\opendevx_rapidoc_formatter\Form;

use Drupal\node\NodeInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form to provide option for the UI formatter for API.
 */
class ApiFormatterOptionsForm extends FormBase {

  /**
   * Current path variable.
   *
   * @var mixed
   */
  protected $currentPath;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_formatter_option_form';
  }

  /**
   * ProductBannerBlock constructor.
   *
   * @param Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   */
  public function __construct(RequestStack $request_stack) {
    $this->currentPath = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_path = $this->currentPath->getCurrentRequest();
    $formatter_option = $current_path->get('view');
    $options = [];
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface &&
      $node->getType() == "api_document") {
      $options = $node->get('field_format')->getValue();
      $allowed_values = $node->getFieldDefinition('field_format')->getFieldStorageDefinition()->getSetting('allowed_values');

      $options = $options ? array_column($options, 'value') : [];
      $options = array_combine($options, $options) ?: [];
      $options = array_intersect_key($allowed_values, $options);
    }

    $form['formatter_options'] = [
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => (!empty($formatter_option) ? $formatter_option : 'redoc_ui'),
      '#ajax' => [
        'callback' => [$this, 'apiFormatSwitcherHandler'],
        'event' => 'change',
      ],
    ];

    return $form;
  }

  /**
   * The callback function for when the `formatter_options` element is changed.
   *
   * The display formatter for the API is changed as per selection.
   */
  public function apiFormatSwitcherHandler(array $form, FormStateInterface $form_state) {
    // Get the value of formatter selected for API display.
    $formatter_option = $form_state->getValue('formatter_options');
    // @todo : Add try catch for any exception.
    $response = new AjaxResponse();
    // Refresh the page to display the api with selected formatter.
    $url = Url::fromRoute('<current>', ['view' => $formatter_option]);
    $command = new RedirectCommand($url->toString());
    $response->addCommand($command);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // ...
  }

}
