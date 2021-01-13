<?php

namespace Drupal\odp_blocks\Utility;

/**
 * Class to extend and provide the features of developer portal.
 */
class ApiFilterationUtility {

  /**
   * {@inheritdoc}
   */
  public static function getApiEnvironemt($parent) {
    try {
      $query = \Drupal::database();
      $query = $query->select('taxonomy_term_field_data', 't');
      $query->addField('t', 'name');
      $query->addField('t', 'tid');
      $query->addJoin('left', 'node_revision__field_environment',
      'nf', 't.tid = nf.field_environment_target_id');
      $query->addJoin('left', 'node__field_api_specifications',
      'nfa', 'nfa.field_api_specifications_target_id = nf.entity_id');
      $query->condition('nfa.entity_id', $parent);
      $environment = $query->distinct()->execute()->fetchAll();
      foreach ($environment as $value) {
        $result[$value->tid] = $value->name;
      }

      return array_unique($result);
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Get API Names callback.
   */
  public static function getApiNames($environment_id, $parent) {
    try {
      $query = \Drupal::database();
      $query = $query->select('node_field_revision', 'n');
      $query->addField('n', 'nid');
      $query->addField('n', 'title');
      $query->addJoin('left', 'node_revision__field_api_specifications',
      'nf', 'n.nid = nf.field_api_specifications_target_id');
      $query->addJoin('left', 'node_revision__field_environment',
      'nrf', 'nf.field_api_specifications_target_id = nrf.entity_id');
      $query->condition('nrf.field_environment_target_id', $environment_id, '=');
      $query->condition('nf.entity_id', $parent, '=');
      $names = $query->distinct()->execute()->fetchAllKeyed(0, 1);
      $names = ['--Select--'] + $names;

      return array_unique($names);
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Get API Versions callback.
   */
  public static function getApiVersions($version_id) {
    try {
      $query = \Drupal::database();
      $query = $query->select('taxonomy_term_field_data', 't');
      $query->addField('t', 'tid');
      $query->addField('t', 'name');
      $query->addField('nf', 'revision_id');
      $query->addJoin('left', 'node_revision__field_api_version',
      'nf', 't.tid = nf.field_api_version_target_id');
      $query->condition('nf.entity_id', $version_id);
      $query->orderBy('nf.revision_id', 'desc');
      $result = $query->distinct()->execute()->fetchAllKeyed(2, 1);
      $result = ['--Select--'] + array_unique($result);

      return array_unique($result);
    }
    catch (\Exception $e) {
    }
  }

}
