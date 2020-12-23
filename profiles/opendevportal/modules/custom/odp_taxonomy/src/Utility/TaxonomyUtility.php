<?php

namespace Drupal\odp_taxonomy\Utility;

class TaxonomyUtility {

  /**
   * Function to get taxonomy list.
   * 
   * @param int $vid
   *    Vocab Id or term id.
   * @param string $type
   *    List or name.
   * 
   * @return array
   *    Taxonomy data.
   */
  public static function getTaxonomyData($id = NULL, $type = NULL) {
    $taxonomy = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    // Check if term name is needed.
    if (!empty($id) && $type == 'name') {
      $term = $taxonomy->load($id);
      
      return $term->getName();
    }
    // Check if vocabulary list is needed.
    if (!empty($id) && $type == 'list') {
      $terms = $taxonomy->loadTree($id);
      $term_data = [];
      foreach ($terms as $term) {
        $term_data[$term->tid]['title'] = $term->name;
        $term_data[$term->tid]['description'] = $term->description__value;
      }

      return $term_data;
    }
  }

}