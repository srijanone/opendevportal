<?php

namespace Drupal\odp_domain\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\group\Entity\GroupInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns responses for programContent routes.
 */
class ProgramsContentController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new ProgramsContentController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager..
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer,
    RequestStack $request_stack) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('request_stack'),
    );
  }

  /**
   * Provides the group content creation overview page.
   */
  public function addPage() {
    // Boolean variable $create_mode.
    $create_mode = TRUE;
    $build = ['#theme' => 'entity_add_list', '#bundles' => []];
    $group = $this->entityTypeManager->getStorage('group')->load(
    $this->requestStack->getCurrentRequest()->get('pid'));
    if ($group) {
      $form_route = $this->addPageFormRoute($group, $create_mode);
      $bundle_names = $this->addPageBundles($group, $create_mode);

      // Set the add bundle message if available.
      $add_bundle_message = $this->addPageBundleMessage($group, $create_mode);
      if ($add_bundle_message !== FALSE) {
        $build['#add_bundle_message'] = $add_bundle_message;
      }

      // Filter out the bundles the user doesn't have access to.
      $access_control_handler = $this->entityTypeManager->getAccessControlHandler('group_content');
      foreach ($bundle_names as $plugin_id => $bundle_name) {
        $access = $access_control_handler->createAccess($bundle_name, NULL, ['group' => $group], TRUE);
        if (!$access->isAllowed()) {
          unset($bundle_names[$plugin_id]);
        }
        $this->renderer->addCacheableDependency($build, $access);
      }

      // Set the info for all of the remaining bundles.
      foreach ($bundle_names as $plugin_id => $bundle_name) {
        $plugin = $group->getGroupType()->getContentPlugin($plugin_id);
        if ($plugin->getEntityBundle()) {
          $label = $this->entityTypeManager->getStorage('node_type')->load($plugin->getEntityBundle())->get('name');
          $build['#bundles'][$bundle_name] = [
            'label' => 'Create ' . $label,
            'description' => $this->t('Adds %type content to programs both publicly and privately.', ['%type' => $label]),
            'add_link' => Link::createFromRoute($label, $form_route, ['group' => $group->id(), 'plugin_id' => $plugin_id]),
          ];
        }
      }

      // Add the list cache tags for the GroupContentType entity type.
      $bundle_entity_type = $this->entityTypeManager->getDefinition('group_content_type');
      $build['#cache']['tags'] = $bundle_entity_type->getListCacheTags();
    }

    return $build ?? [];
  }

  /**
   * Retrieves a list of available bundles for the add page.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to add the group content to.
   * @param bool $create_mode
   *   Whether the target entity still needs to be created.
   *
   * @return array
   *   An array of group content type IDs, keyed by the plugin that was used to
   *   generate their respective group content types.
   *
   * @see ::addPage()
   */
  protected function addPageBundles(GroupInterface $group, $create_mode) {
    $bundles = [];

    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    foreach ($storage->loadByGroupType($group->getGroupType()) as $bundle => $group_content_type) {
      // Skip the bundle if we are listing bundles that allow you to create an
      // entity in the group and the bundle's plugin does not support that.
      if ($create_mode && !$group_content_type->getContentPlugin()->definesEntityAccess()) {
        continue;
      }

      $bundles[$group_content_type->getContentPluginId()] = $bundle;
    }

    return $bundles;
  }

  /**
   * Returns the 'add_bundle_message' string for the add page.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to add the group content to.
   * @param bool $create_mode
   *   Whether the target entity still needs to be created.
   *
   * @return string|false
   *   The translated string or FALSE if no string should be set.
   *
   * @see ::addPage()
   */
  protected function addPageBundleMessage(GroupInterface $group, $create_mode) {
    // We do not set the 'add_bundle_message' variable because we deny access to
    // the page if no bundle is available. This method exists so that modules
    // that extend this controller may specify a message should they decide to
    // allow access to their page even if it has no bundles.
    return FALSE;
  }

  /**
   * Returns the route name of the form the add page should link to.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to add the group content to.
   * @param bool $create_mode
   *   Whether the target entity still needs to be created.
   *
   * @return string
   *   The route name.
   *
   * @see ::addPage()
   */
  protected function addPageFormRoute(GroupInterface $group, $create_mode) {
    return $create_mode
      ? 'entity.group_content.create_form'
      : 'entity.group_content.add_form';
  }

}
