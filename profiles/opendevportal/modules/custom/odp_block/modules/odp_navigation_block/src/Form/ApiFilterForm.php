<?php
/**
 * @file
 * Contains \Drupal\odp_navigation_block\Form\ApiFilter.
 */
namespace Drupal\odp_navigation_block\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\odp_navigation_block\Utility\ApiFilterationUtility;

class ApiFilterForm extends FormBase {

  /**
   * Current path variable.
   *
   * @var mixed
   */

  protected $currentPath;

  /**
   * ApiFilterForm constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The plugin request stack service.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity Manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager, AliasManagerInterface $alias_manager) {
    $this->currentPath = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('path.alias_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->parent = $this->currentPath->getCurrentRequest()->get('parent');
    $path = $this->currentPath->getCurrentRequest()->getPathInfo();
    $url_alias = $this->aliasManager->getPathByAlias($path);
    $explode_path = explode('/', $url_alias);
    $node = $this->entityTypeManager->getStorage('node')->load($explode_path[2]);

    if ($node) {
      $environment_id = $node->get('field_environment')->getValue()[0]['target_id'];
      $this->nid = $node->id();
      $vid = $node->vid->value;
    }

    $form['environment'] = [
      '#type' => 'select',
      '#title' => $this->t('API Environment'),
      '#options' => ApiFilterationUtility::getApiEnvironemt($this->parent),
      '#default_value' => $environment_id,
    ];

    $form['title'] = [
      '#type' => 'select',
      '#title' => $this->t('API Name'),
      '#options' => ApiFilterationUtility::getApiNames($environment_id, $this->parent),
      '#default_value' => $this->nid,
    ];

    $form['version'] = [
      '#type' => 'select',
      '#title' => $this->t('API Version'),
      '#options' => ApiFilterationUtility::getApiVersions($this->nid),
      '#default_value' => $explode_path[4] ? $explode_path[4] : $vid,
    ];

    $form['product_id'] = [
      '#type' => 'hidden',
      '#value' => $this->parent,
      '#attributes' => ['class' => ['parent_id']],
    ];

    $form['#attached'] = [
      'library' => [
        'odp_navigation_block/api_filteration',
      ],
    ];
    $form['#cache']['max-age'] = 0;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
