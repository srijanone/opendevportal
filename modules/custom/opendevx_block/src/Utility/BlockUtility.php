<?php

namespace Drupal\opendevx_block\Utility;

use Drupal\node\NodeInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Class to extend and provide the features of developer portal.
 */
class BlockUtility {

  /**
   * Prepare dashboard navigation block.
   *
   * @param array $bundles
   *   Set of bundles in system.
   * @param int $pid
   *   The product Id.
   *
   * @return string
   *   String value of node data.
   */
  public static function prepareDashboardNavBlock(array $bundles, $pid) {
    $output = [];
    // API Product service.
    $product = \Drupal::service('opendevx_block.products');
    $product_title = $product->setProductId($pid)->getTitle();
    foreach ($bundles as $key => $value) {
      // Refer content type linked to the product.
      $path = "/dashboard/contents/$key/$pid";
      $description = $value['description'];
      $label = $value['label'];
      $description = strip_tags($description);
      $output[$product_title['title']][$label]['childPath'] = $path;
      $output[$product_title['title']][$label]['childDescription'] = $description;
    }
    return $output;
  }

  /**
   * Fetches the content type lists in system.
   *
   * @return array
   *   Set of bundles in the system.
   */
  public static function getBundleInfo(array $bundle_info = []) {
    $types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    foreach ($types as $key => $value) {
      $bundle[$key]['label'] = $value->get('name');
      if (in_array('description', $bundle_info)) {
        $bundle[$key]['description'] = $value->getDescription();
      }
    }

    return $bundle;
  }

  /**
   * Function to get node id by url path.
   *
   * @param mixed $current_path
   *   Request instance.
   * @param string $type
   *   Url type.
   *
   * @return int
   *   Id.
   */
  public static function getIdByPath($current_path, $type = NULL) {
    $content_types = [
      'apps', 'article', 'assets', 'document_overview', 'events', 'faq',
      'forum', 'issues', 'resources', 'solutions', 'tutorials', 'use_cases', 'api_document',
    ];
    $path = $current_path->getPathInfo();
    $path_index = explode('/', $path);
    if (!empty($type)) {
      $index = explode('/', $path);
      return $index[4];
    }
    $url_alias = \Drupal::service('path.alias_manager')->getPathByAlias($path);
    if (preg_match('/node\/(\d+)/', $url_alias, $matches)) {
      $nid = $matches[1];
    }
    if (isset($path_index[3]) && in_array($path_index[3], $content_types)) {
      unset($path_index[3]);
      $new_path = implode('/', $path_index);
      $url_alias = \Drupal::service('path.alias_manager')->getPathByAlias($new_path);
      if (preg_match('/node\/(\d+)/', $url_alias, $matches)) {
        $nid = $matches[1];
      }
    }
    $parent = $current_path->get('parent');
    if (!$parent) {
      // Get current node.
      $node = \Drupal::routeMatch()->getParameter('node');
      if ($node instanceof NodeInterface) {
        if ($node->hasField('field_api_product') && !empty($node->get('field_api_product')->first())) {
          $parent = $node->get('field_api_product')->first()->getValue()['target_id'];
        }
      }
    }

    $id = (int) ((isset($parent)) ? $parent : $nid);
    if (!empty($id)) {
      return $id;
    }
  }

  /**
   * Function to prepare sidebar navigation block.
   *
   * @param mixed $result
   *   Result set.
   * @param int $id
   *   Node Id.
   * @param array $list
   *   Navigation list.
   *
   * @return mixed
   *   Navigation data.
   */
  public static function prepareNavigationBlock($result, $id, array $list) {
    $output = [];
    $content_types = [
      'apps', 'article', 'assets', 'document_overview', 'events', 'faq',
      'forum', 'issues', 'resources', 'solutions', 'tutorials', 'use_cases', 'api_document',
    ];
    if (!empty($result)) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
      $node_view_mode = $node->get('field_view_mode')->getValue()[0]['value'];
      $current_path = \Drupal::service('path.current')->getPath();
      $path_index = explode('/', $current_path);
      $child = $result['child'];
      if (!empty($result['api'])) {
        $child = $result['child'] . ',api_document';
      }
      $childs = explode(",", $child);
      $iterate = array_intersect(array_keys($list), $childs);
      // Root of the sidebar.
      // Title of the product.
      foreach (array_flip($iterate) as $key => $value) {
        $branch = ucwords($list[$key]);
        if ($node_view_mode == 'left_nav_full_view' || in_array($path_index[3], $content_types)) {
          $path = "/node/$id";
          $content_path = \Drupal::service('path.alias_manager')->getAliasByPath($path);
          $path_alias = "$content_path/$key";
        }
        else {
          $path = str_replace("_", "-", $key);
          $path_alias = "/products/$path/list?parent=$id";
          // Check for api document.
          if ($key == 'api_document') {
            $path_alias = "/node/" . $result['api_id'] . "?parent=$id";
          }
        }
        // Refer content type linked to the product.
        $output[$result['title']][$branch]['childPath'] = $path_alias;
        $output[$result['title']][$branch]['childClass'] = "child_$key";
      }

      return $output;
    }
  }

  /**
   * Function to generate media image.
   *
   * @param int $mid
   *   Media ID.
   * @param string $style
   *   Style name.
   *
   * @return mixed
   *   Image path.
   */
  public static function generateMediaImage($mid, $style) {
    $image = "";
    $fid = Media::load($mid)->field_media_image[0]->getValue()['target_id'];
    if (!empty($fid)) {
      $file = File::load((int) $fid);
      $style = \Drupal::entityTypeManager()->getStorage('image_style')->load($style);
      $image = $style->buildUrl($file->getFileUri());
    }

    return $image;
  }

}
