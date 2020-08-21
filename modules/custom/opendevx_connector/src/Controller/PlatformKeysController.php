<?php

namespace Drupal\opendevx_connector\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides controller class PlatformKeysController.
 */
class PlatformKeysController extends ControllerBase {


  /**
   * The request stack instance.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $currentPath;

  /**
   * The account instance which represents current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * PlatformKeysController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The plugin account service.
   */
  public function __construct(RequestStack $request_stack,
  AccountInterface $account) {
    $this->currentPath = $request_stack;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('current_user')
    );
  }

  /**
   * Render program keys' configured values.
   */
  public function content($program = NULL) {
    // Load configurations.
    $get_all_config = \Drupal::config("opendevx_connector.program_keys.settings")->get();
    // Get user's all organisations.
    $user_organisations = \Drupal::service('opendevx_user.organisation')->getUserOrganisations();
    // Return if not a valid program.
    if ($program->getVocabularyId() != 'organisation' ||
    !in_array($program->id(), array_column($user_organisations, 'orgId'))) {
      throw new NotFoundHttpException();
    }
    // If everything goes well, set current program id as orgId.
    $this->orgId = $program->id();
    // Platform Keys configurations.
    $config = [];
    // Config pattern to differentiate organisations.
    $pattern = '_' . $this->orgId;
    $pattern_length = strlen($pattern);
    // Filter out the required configurations.
    foreach ($get_all_config as $key => $value) {
      if (substr($key, -$pattern_length) === $pattern) {
        $config[substr($key, 0, -$pattern_length)] = $value;
      }
    }

    return [
      '#markup' => $this->t('Program platform key details'),
      '#theme' => 'opendevx_connector_theme_programkeys',
      '#details' => array_filter($config),

    ];
  }

}
