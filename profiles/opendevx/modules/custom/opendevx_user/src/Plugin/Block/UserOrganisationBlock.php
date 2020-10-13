<?php

namespace Drupal\opendevx_user\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opendevx_user\Organisation;

/**
 * Provides a 'User Program' Block.
 *
 * @Block(
 *   id = "user_organisation_block",
 *   admin_label = @Translation("User Program Block"),
 *   category = @Translation("User Program Block"),
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
      $container->get('opendevx_user.organisation'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_program = $title = $description = '';
    $config = $this->getConfiguration();
    $data = $this->org->getUserOrganisations();
    $title = $config['program_title'];
    $description = strip_tags($config['program_description']);
    $program_service = \Drupal::service('opendevx_user.organisation');
    $program_id = $program_service->getOrgId() ?: 0;
    $user_roles = \Drupal::service('tempstore.private')->get('opendevx_user')->get('user_programs');
    $program_ids = array_keys($user_roles);
    $non_member = array_diff(array_keys($data), $program_ids);
    foreach ($non_member as $value) {
      unset($data[$value]);
    }
    $current_program = $data[$program_id]['orgName'];

    return [
      '#theme' => 'user_organisation',
      '#orgData' => $data,
      '#orgTitle' => $title,
      '#orgDescription' => $description,
      '#currentOrganisation' => $current_program,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['program_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Display Title'),
      '#default_value' => $config['program_title'],
    ];
    $form['program_description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
      '#format'=> 'full_html',
      '#default_value' => $config['program_description'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Setting value for Program fields.
    $this->setConfigurationValue('program_title', $form_state->getValue('program_title'));
    $this->setConfigurationValue('program_description', $form_state->getValue('program_description')['value']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
