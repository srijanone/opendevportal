<?php

namespace Drupal\developer_portal_connector\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;

class PlatformKeysController extends ControllerBase {


  /**
   * @var mixed member variable $orgId.
   */
  protected $orgId;

  /**
   * @var mixed $currentPath
   */
  protected $currentPath;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * PlatformKeysController constructor.
   *
   * @param mixed $product
   *   The plugin api product class.
   * @param mixed $request_stack
   *   The plugin request stack service.
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
    $get_all_config = \Drupal::config("developer_portal_connector.program_keys.settings")->get();
    // Get user's all organisations.
    $user_organisations = \Drupal::service('opendevx_user.organisation')->getUserOrganisations();
    // Return if not a valid program.
    if ($program->getVocabularyId() != 'organisation' ||
    !in_array($program->id(), array_column($user_organisations, 'orgId'))) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
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
      if (substr($key, - $pattern_length) === $pattern) {
        $config[substr($key, 0, - $pattern_length)] = $value;
      }
    }

    return [
      '#markup' => $this->t('Program platform key details'),
      '#theme' => 'developer_portal_connector_theme_programkeys',
      '#details' => array_filter($config),

    ];
  }

}
