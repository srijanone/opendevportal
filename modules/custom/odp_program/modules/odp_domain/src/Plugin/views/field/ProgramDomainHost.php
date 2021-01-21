<?php

namespace Drupal\odp_domain\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * A handler to provide a domain hostname field.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("program_domain_host")
 */
class ProgramDomainHost extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (!empty($values)) {
      $gid = $values->_entity->get('id')->value;
      $program_domain_url = \Drupal::service('path.alias_manager')->getAliasByPath("/group/$gid");
      $program_target = "_self";
      $group_domain = \Drupal::entityTypeManager()->getStorage('domain')->load('group_' . $gid);
      if ($group_domain) {
        $program_domain_url = $group_domain->getPath();
        $program_target = "_blank";
      }
      $current_path = \Drupal::service('path.current')->getPath();
      $path = ['/dashboard/manage/program'];
      if (in_array($current_path, $path)) {
        $label = $values->_entity->label();
      }
      else {
        $label = $this->t('Explore');
      }
      return [
        '#markup' => '<a href="' . $program_domain_url . '" target="' . $program_target . '">' . $label . '</a>',
      ];
    }
    else {
      return [
        '#markup' => $this->t('There is no program is added yet.'),
      ];
    }
  }

}
