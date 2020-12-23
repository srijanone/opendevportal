<?php

namespace Drupal\odp_block\Utility;

use Drupal\views\Views;

/**
 * Purpose of this class is to get results for dynamic listings of block.
 */
class ListingBlockUtility {

  /**
   * Get view contents.
   *
   * @param array $filters
   *   Array of filters.
   * @param string $type
   *   View name.
   * @param string $display
   *   Display name.
   *
   * @return array
   *    Renderable block data.
   */
  public static function getViewResults($filters, $type, $display, $pager = FALSE, $nid = FALSE) {
    $arguments = [implode("+", $filters)];
    $view = Views::getView($type);
    $view->setDisplay($display);
    $view->setArguments($arguments);
    if ($nid) {
      $arg_param = $view->args;
      array_push($arg_param, $nid);
      $view->setArguments($arg_param);
    }
    $view->setItemsPerPage($pager);
    $path = "/node/" . $nid . "/" . $arguments[0];
    // Add footer of view.
    if ($display != 'block_dynamic_content') {
      $options = (new self)->getViewOption($path);
      $view->setHandler('content_listing_block_display', 'footer', 'area_text_custom', $options);
    }
    $view->execute();
    if ($view->result) {
      $content = $view->buildRenderable($display);
      return [$content];
    }
  }

  /**
   * Get view footer contents.
   *
   * @param string $path
   *   Path of view.
   *
   * @return array
   *    Renderable footer data.
   */
  public function getViewOption($path) {
    $options = array(
      'id' => 'area_text_custom',
      'table' => 'views',
      'field' => 'area_text_custom',
      'relationship' => 'none',
      'group_type' => 'none',
      'admin_label' => '',
      'content' => '<a href = ' . $path . '>More Items</a>',
      'empty' => TRUE,
      'tokenize' => FALSE,
      'plugin_id' => 'text_custom',
    );

    return $options;
  }

}
