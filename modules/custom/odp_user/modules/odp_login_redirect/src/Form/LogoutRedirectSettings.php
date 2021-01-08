<?php

namespace Drupal\odp_login_redirect\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class definition of logout redirect settings form.
 */
class LogoutRedirectSettings extends SettingsBaseForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->setEvent('logout');

    return parent::buildForm($form, $form_state);
  }

}
