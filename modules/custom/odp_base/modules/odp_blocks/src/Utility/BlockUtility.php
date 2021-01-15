<?php

namespace Drupal\odp_blocks\Utility;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\NodeInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\odp_product\ContentInterface;
use Drupal\odp_user\ProgramInterface;

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
   *   The product ID.
   *
   * @return string
   *   String value of node data.
   */
  public static function prepareDashboardNavBlock(array $bundles, $pid) {
    $output = [];
    // API Product service.
    $product_title = \Drupal::service('odp_blocks.products')->setProductId($pid)->getTitle();
    foreach ($bundles as $key => $value) {
      $program_id = \Drupal::service('odp_user.organisation')->getOrgId();
      $label = $value['label'];
      $output[$product_title['title']][$label]['childPath'] = "/dashboard/$program_id/contents/$key/$pid";
      $output[$product_title['title']][$label]['childDescription'] = strip_tags($value['description']);
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
    $nid = 0;
    $path = $current_path->getPathInfo();
    if (!empty($type)) {
      $index = explode('/', $path);
      return $index[5];
    }
    $path_index = explode('/', $path);
    $url_alias = \Drupal::service('path.alias_manager')->getPathByAlias($path);
    if (preg_match('/node\/(\d+)/', $url_alias, $matches)) {
      $nid = $matches[1];
    }
    if (isset($path_index[3]) && in_array($path_index[3], ContentInterface::CONTENT_TYPES)) {
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
   * @param array $result
   *   Result set.
   * @param int $id
   *   Node Id.
   * @param array $list
   *   Navigation list.
   *
   * @return mixed
   *   Navigation data.
   */
  public static function prepareNavigationBlock(array $result, $id, array $list) {
    $output = [];
    if (!empty($result)) {
      $child = $result['child'];
      if (!empty($result['api'])) {
        $child = $result['child'] . ',api_document,resources';
      }
      $childs = explode(",", $child);
      $iterate = array_intersect(array_keys($list), $childs);
      // Root of the sidebar. Title of the product.
      foreach (array_flip($iterate) as $key => $value) {
        $branch = ucwords($list[$key]);
        $path = "/node/$id";
        $content_path = \Drupal::service('path.alias_manager')->getAliasByPath($path);
        // Refer content type linked to the product.
        $output[$result['title']][$branch]['childPath'] = "$content_path/$key";
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

  /**
   * Check block access.
   *
   * @param \Drupal\block\Entity\Block $block
   *   Block object.
   *
   * @return void
   *   Returns access flags.
   */
  public static function checkBlockAccess($block) {
    switch ($block->id()) {
      // Visible to PM only.
      case 'pm_dashboard':
      case 'productmanagement':
      case 'productmanagement_2':
        if (self::checkPmRole() == FALSE) {
          return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
        }
        break;

      case 'user_organisation_block':
        $user_roles = \Drupal::service('tempstore.private')->get('odp_user')->get('user_programs');
        $roles = array_unique(array_intersect($user_roles, ProgramInterface::PM_ROLES));
        if (count($roles) < 1) {
          return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
        }
        break;

      // Visible to Dev only.
      case 'developermenu':
        if (self::checkPmRole() == FALSE) {
          return AccessResult::neutral();
        }
        break;

      case 'breadcrumbs':
        $path = \Drupal::service('path.current')->getPath();
        $path_index = explode('/', $path);
        if ($path_index[1] == 'dashboard' || (isset($path_index[3]) && !empty($path_index[3]))) {
          return;
        }
        if ($group = \Drupal::routeMatch()->getParameter('group')) {
          if (is_object($group) && method_exists($group, 'bundle')) {
            if (in_array($group->bundle(), ['private', 'public', 'protected'])) {
              return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
            }
          }
        }
        break;

      // Visible to Dev only.
      case 'odp_theme_main_menu':
      case 'main_menu':
      case 'devportal_admin_main_menu':
      case 'main_menu':
        if (self::checkPmRole() == TRUE) {
          return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
        }
        break;

      case 'local_tasks':
        if (\Drupal::currentUser()->isAnonymous()) {
          return AccessResult::neutral();
        }
        if (FALSE == \Drupal::service('odp_user.organisation')->checkAccess(TRUE)) {
          $path = \Drupal::service('path.current')->getPath();
          $path_index = explode('/', $path);
          if ($path_index[1] == 'dashboard' && (isset($path_index[2]) && $path_index[2] == 'topic')) {
            return AccessResult::neutral();
          }
        }

        $exclude_routes = ['entity.user.canonical'];
        if (in_array(\Drupal::request()->attributes->get('_route'), $exclude_routes)) {
          return AccessResult::neutral();
        }
        elseif (FALSE == \Drupal::service('odp_user.organisation')->checkAccess()) {
          return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
        }
        break;

      case 'account_menu':
      case 'devportal_admin_account_menu':
        if (\Drupal::currentUser()->isAnonymous()) {
          return AccessResult::neutral();
        }
        elseif (TRUE == \Drupal::service('odp_user.organisation')->checkAccess(TRUE)) {
          return AccessResult::neutral();
        }
        else {
          return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
        }
        break;

      case 'developerusermenu':
      case 'developerusermenu_2':
        if (TRUE == \Drupal::service('odp_user.organisation')->checkAccess(TRUE)) {
          return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
        }
        else {
          return AccessResult::neutral();
        }
        break;

      case 'product_api':
      case 'product_banner':
        if (!empty(\Drupal::request()->get('parent'))) {
          return AccessResult::neutral();
        }
        $node = \Drupal::routeMatch()->getParameter('node');
        if (($node instanceof NodeInterface) && ($node->gettype() == 'api_product')) {
          return AccessResult::neutral();
        }
        $path_index = explode('/', \Drupal::service('path.current')->getPath());
        if ($block->id() == 'product_banner' && $path_index[1] == 'product') {
          return AccessResult::neutral();
        }
        if ((isset($path_index[3]) && in_array($path_index[3], ContentInterface::CONTENT_TYPES))) {
          return AccessResult::neutral();
        }
        else {
          // Hiding the block if the node is not 'api_product' type.
          return AccessResult::forbiddenIf(TRUE)->addCacheableDependency($block);
        }
        break;
    }
  }

  /**
   * Check if user is a PM or not.
   *
   * Return int.
   */
  public static function checkPmRole() {
    $user_roles = \Drupal::service('odp_user.organisation')->getUserGroupRole();
    if (count(array_intersect($user_roles, ProgramInterface::PM_ROLES)) > 0) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
