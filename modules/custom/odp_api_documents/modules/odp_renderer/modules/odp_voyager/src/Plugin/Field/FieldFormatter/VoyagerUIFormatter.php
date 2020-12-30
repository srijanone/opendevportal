<?php

namespace Drupal\odp_voyager\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'Voyager' UI formatter.
 *
 * @FieldFormatter(
 *   id = "voyager_ui",
 *   label = @Translation("Voyager UI"),
 *   description = @Translation("Formats file fields with GraphQL Voyager YAML or JSON
 *   files with a rendered Voyager UI"), field_types = {
 *     "file"
 *   }
 * )
 */
class VoyagerUIFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return parent::settingsSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);
    $elements['#attached']['library'][] = 'odp_voyager/graphQL_voyager';
    foreach ($this->getEntitiesToView($items, $langcode) as $file) {
      $voyager_file = file_create_url($file->getFileUri());
      $elements['#attached']['drupalSettings']['odp_voyager']['document'] = $voyager_file;
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $element[] = [
      '#theme' => 'voyager_ui_field_item',
    ];
    return $element;
  }

}
