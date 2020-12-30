<?php

namespace Drupal\odp_login_redirect\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class definition of login redirect settings form.
 */
class LoginRedirectSettings extends SettingsBaseForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->setEvent('login');

    return parent::buildForm($form, $form_state);
  }

}
