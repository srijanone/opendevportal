<?php

namespace Drupal\odp_user\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\RoleInterface;

/**
 * Class SetOrganisationForm to render organization.
 */
class SetOrganisationForm extends FormBase {

  /**
   * Organisation.
   *
   * @var \Drupal\odp_user\Organisation
   */
  protected $org;

  /**
   * Default class constructor.
   */
  public function __construct() {
    $this->org = \Drupal::service('odp_user.organisation');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'organisation_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $get_organisation = $this->org->getUserOrganisations();
    $options = array_combine(array_keys($get_organisation),
    array_column($get_organisation, 'orgName'));
    $org_id = ($this->org->getOrgId()) ?: NULL;
    $form['organisation'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Program'),
      '#options' => $options ?: [],
      '#default_value' => $org_id,
      '#required' => TRUE,
    ];
    // Add hidden submit button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Start'),
      '#attributes' => !empty($options) ? [] : ['disabled' => 'disabled'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($orgId = $form_state->getValue('organisation')) {
      // Set value.
      $this->org->setProgramId($orgId);
      $roles = \Drupal::service('entity_type.manager')->getStorage('user_role')->loadMultiple();

      foreach ($roles as $role) {
        if ($role instanceof RoleInterface) {
          switch ($role->id()) {
            case "product_manager":
              // See Views for details /admin/structure/views/view/
              // dashboard_listing/edit/page_product_management.
              $path = "/dashboard/products";
              break;

            default;
          }
        }
      }
      $response = new RedirectResponse($path);
      $response->send();
    }
  }

}
