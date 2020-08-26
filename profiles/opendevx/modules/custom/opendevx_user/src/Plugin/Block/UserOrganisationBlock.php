<?php

namespace Drupal\opendevx_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opendevx_user\Organisation;

/**
 * Provides a 'User Organisation' Block.
 *
 * @Block(
 *   id = "user_organisation_block",
 *   admin_label = @Translation("User Organisation Block"),
 *   category = @Translation("User Organisation Block"),
 * )
 */
class UserOrganisationBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * UserOrganisation object.
   *
   * @var \Drupal\opendevx_user\UserOrganisation $org
   *
   */
  protected $org;

   /**
   * UserOrganisationBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param mixed $organisation
   *   The plugin organisation class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Organisation $organisation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->org = $organisation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('opendevx_user.organisation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $org_id = $current_organisation = $title = $description = '';
    $config = $this->getConfiguration();
    $data = $this->org->getUserOrganisations();
    $title = $config['organisation_title'];
    $description = strip_tags($config['organisation_description']);
    $org_id = $this->org->getOrgId();
    $current_organisation = $data[$org_id]['orgName'];

    return [
      '#theme' => 'user_organisation',
      '#orgData' => $data,
      '#orgTitle' => $title,
      '#orgDescription' => $description,
      '#currentOrganisation' => $current_organisation,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['organisation_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Display Title'),
      '#default_value' => $config['organisation_title'],
    ];
    $form['organisation_description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#format'=> 'full_html',
      '#default_value' => $config['organisation_description'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Setting value for organisations fields.
    $this->setConfigurationValue('organisation_title', $form_state->getValue('organisation_title'));
    $this->setConfigurationValue('organisation_description', $form_state->getValue('organisation_description')['value']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
