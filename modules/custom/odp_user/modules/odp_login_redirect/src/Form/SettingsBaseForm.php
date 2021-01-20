<?php

namespace Drupal\odp_login_redirect\Form;

use Drupal\Core\Utility\Token;
use Drupal\user\RoleInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class definition of settings base form.
 */
class SettingsBaseForm extends ConfigFormBase {

  /**
   * Module key name for settings and form_id.
   *
   * @var string
   */
  const KEY = 'odp_login_redirect';

  /**
   * Event key for settings.
   *
   * @var string
   */
  protected $event = 'login';

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs a SiteInformationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    PathValidatorInterface $path_validator,
    EntityTypeManagerInterface $entity_type_manager,
    ModuleHandlerInterface $module_handler,
    Token $token) {
    parent::__construct($config_factory);
    $this->pathValidator = $path_validator;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('path.validator'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::KEY . '.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::KEY . '_login_settings';
  }

  /**
   * Set event value.
   *
   * @param string $event
   *   Event name: login|logout.
   */
  public function setEvent($event) {
    $this->event = $event;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::KEY . '.settings');
    $containers = $this->getRoleContainers();

    foreach ($containers as $key => $value) {
      $container = $key . '_detail';

      $form[$container] = [
        '#type' => 'details',
        '#title' => $value['label'],
        '#open' => TRUE,
        '#suffix' => '<br>',
      ];

      $form[$container][$key] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Role'),
          $this->t('Redirect URL'),
          $this->t('Allow destination'),
          $this->t('Replace pattern'),
          $this->t('Weight'),
        ],
        '#caption' => $this->t("If you don't need redirection - leave Redirect URL empty.
          We are supporting only %group replacement with user's current selected program."),
        '#empty' => $this->t('Sorry, There are no items!'),
        '#tabledrag' => [
          [
            'action' => 'order',
            'relationship' => 'sibling',
            'group' => 'table-sort-weight',
          ],
        ],
      ];

      foreach ($value['roles'] as $role_id => $role_name) {
        $config_row = $config->get($this->event . "." . $key . '.' . $role_id);

        $form[$container][$key][$role_id]['#attributes']['class'][] = 'draggable';
        $form[$container][$key][$role_id]['#weight'] = isset($config_row['weight']) ? $config_row['weight'] : 0;

        $form[$container][$key][$role_id]['role'] = [
          '#markup' => $role_name,
        ];
        $form[$container][$key][$role_id]['redirect_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Redirect URL'),
          '#title_display' => 'invisible',
          '#default_value' => isset($config_row['redirect_url']) ? $config_row['redirect_url'] : '',
        ];
        $form[$container][$key][$role_id]['allow_destination'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Allow destination'),
          '#title_display' => 'invisible',
          '#default_value' => (bool) $config_row['allow_destination'],
        ];
        $form[$container][$key][$role_id]['replace_pattern'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Replace pattern'),
          '#title_display' => 'invisible',
          '#default_value' => (bool) $config_row['replace_pattern'],
        ];
        $form[$container][$key][$role_id]['weight'] = [
          '#type' => 'weight',
          '#title' => $this->t('Weight for @role', ['@role' => $role_name]),
          '#title_display' => 'invisible',
          '#default_value' => $form[$container][$key][$role_id]['#weight'],
          '#attributes' => ['class' => ['table-sort-weight']],
        ];
      }

      Element::children($form[$container][$key], TRUE);
    }

    $form['hint'] = [
      '#type' => 'details',
      '#title' => $this->t('Working logic'),
      '#description' => $this->t('Roles order in list is their priorities: higher in list - higher priority.<br>For example: You set roles ordering as:<br>+ Admin<br>+ Manager<br>+ Authenticated<br>it means that when some user log in (or log out) module will check:<br><em>Does this user have Admin role?</em><ul><li>Yes and Redirect URL is not empty - redirect to related URL</li><li>No or Redirect URL is empty:</li></ul><em>Does this user have Manager role?</em><ul><li>Yes and Redirect URL is not empty - redirect to related URL</li><li>No or Redirect URL is empty:</li></ul><em>Does this user have Authenticated role?</em><ul><li>Yes and Redirect URL is not empty - redirect to related URL</li><li>No or Redirect URL is empty - use default Drupal action</li></ul>'),
      '#open' => FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config(self::KEY . '.settings');
    $containers = $this->getRoleContainers();
    foreach ($containers as $key => $value) {
      $config->set($this->event . "." . $key, $form_state->getValue($key));
    }

    $config->save();
  }

  /**
   * Get available role containers with roles.
   *
   * @return array
   *   Return available roles with their respective container.
   */
  private function getRoleContainers() {
    $containers = [
      'system_roles' => [
        'label' => $this->t('System Roles'),
        'roles' => $this->getSystemRoles(),
      ],
    ];
    $containers += $this->getGroupTypes();

    return $containers;
  }

  /**
   * Get available system roles.
   *
   * @return array
   *   Available system role names.
   */
  protected function getSystemRoles() {
    $names = [];
    $roles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    if (isset($roles[RoleInterface::ANONYMOUS_ID])) {
      unset($roles[RoleInterface::ANONYMOUS_ID]);
    }
    foreach ($roles as $role) {
      if ($role instanceof RoleInterface) {
        $names[$role->id()] = $role->label();
      }
    }

    return $names;
  }

  /**
   * Get all the available group types.
   *
   * @return array
   *   Return available group type with the defined roles in the group.
   */
  private function getGroupTypes() {
    $containers = [];
    $group_types = $this->entityTypeManager->getStorage('group_type')->loadMultiple();
    foreach ($group_types as $type) {
      $roles = $this->getGroupRoles($type->id());
      if (!empty($roles)) {
        $containers[$type->id()] = [
          'label' => $type->label(),
          'roles' => $roles,
        ];
      }
    }

    return $containers;
  }

  /**
   * Get a list of all defined group roles in a group type.
   *
   * @return array
   *   Return available roles in the given group type.
   */
  private function getGroupRoles($group_type) {
    $roles = [];
    $group_roles = $this->entityTypeManager->getStorage('group_role')->loadByProperties([
      'group_type' => $group_type,
      'internal' => FALSE,
    ]);

    /** @var  \Drupal\group\Entity\GroupRole $group_role */
    foreach ($group_roles as $role_id => $group_role) {
      $roles[$role_id] = $group_role->label();
    }

    return $roles;
  }

}
