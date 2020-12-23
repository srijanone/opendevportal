<?php

namespace Drupal\opendevportal\Installer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the site configuration form.
 */
class ModuleConfigureForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opendevportal_module_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Please select modules that you would like to install'),
    ];
    $form['install_modules'] = [
      '#type' => 'container',
    ];
    // List of optional modules.
    $modules = [
      [
        'id' => 'opendevportal_demo',
        'label' => $this->t('OpenDevPortal Demo Content'),
        'description' => $this->t('Installs content which allows you to explore features.'),
      ]
    ];
    foreach ($modules as $module) {
      $form['install_modules_' . $module['id']] = [
        '#type' => 'checkbox',
        '#title' => $module['label'],
        '#description' => isset($module['description']) ? $module['description'] : '',
        '#default_value' => 0,
      ];
    }
    $form['#title'] = $this->t('Install modules');
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and continue'),
      '#button_type' => 'primary',
      '#submit' => ['::submitForm'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $installModules = [];
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'install_modules') !== FALSE && $value) {
        preg_match('/install_modules_(?P<name>\w+)/', $key, $values);
        $installModules[] = $values['name'];
      }
    }
    $buildInfo = $form_state->getBuildInfo();
    $install_state = $buildInfo['args'];
    $install_state[0]['opendevportal_additional_modules'] = $installModules;
    $install_state[0]['form_state_values'] = $form_state->getValues();
    $buildInfo['args'] = $install_state;
    $form_state->setBuildInfo($buildInfo);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

}
