<?php

namespace Drupal\odp_user\EventSubscriber;

use Drupal\odp_user\Organisation;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Form alter event subscriber class.
 */
class FormAlterSubscriber implements EventSubscriberInterface {

  /**
   * Current Path.
   *
   * @var mixed
   */
  protected $currentPath;

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * User program service.
   *
   * @var Drupal\odp_user\Organisation
   */
  protected $program;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    Organisation $program) {
    $this->currentPath = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->program = $program;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'alterForm',
    ];
  }

  /**
   * Alter form.
   *
   * @param \Drupal\core_event_dispatcher\Event\Form\FormAlterEvent $event
   *   The event.
   */
  public function alterForm(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $destination = explode("/", $this->currentPath->getCurrentRequest()->query->get('destination'));
    $group_forms = [
      'group_private_add_form',
      'group_protected_add_form',
      'group_public_add_form',
      'group_private_edit_form',
      'group_protected_edit_form',
      'group_public_edit_form',
    ];
    $group_member_forms = [
      'group_content_public-group_membership_add_form', 'group_content_public-group_membership_edit_form',
      'group_content_private-group_membership_add_form', 'group_content_private-group_membership_edit_form',
      'group_content_protected-group_membership_add_form', 'group_content_protected-group_membership_edit_form',
    ];
    if (in_array($form['#form_id'], $group_forms)) {
      if (FALSE == $this->program->checkAccess()) {
        $form['field_gateway']['#access'] = FALSE;
      }
      $form['actions']['submit']['#value'] = t('Save');
    }
    elseif (in_array($form['#form_id'], $group_member_forms)) {
      // Make the role button required and radio.
      $form['group_roles']['widget']['#type'] = 'radios';
      $form['group_roles']['widget']['#required'] = TRUE;
      $form['group_roles']['widget']['#default_value'] = $form['group_roles']['widget']['#default_value'][0];
    }
    $route = $this->currentPath->getCurrentRequest()->attributes->get('_route');
    if (!empty($destination[5]) && (in_array($route, [
      "entity.node.edit_form",
      "entity.group_content.create_form",
    ]))) {
      if ($route == "entity.group_content.create_form") {
        $form['field_api_product']['#access'] = FALSE;
      }
      $form['field_api_product']['widget'][0]['target_id']['#default_value'] = $this->entityTypeManager->getStorage('node')->load($destination[5]);
    }
  }

}
