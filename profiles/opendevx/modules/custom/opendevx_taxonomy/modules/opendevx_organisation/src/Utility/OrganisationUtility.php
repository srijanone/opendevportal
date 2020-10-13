<?php

namespace Drupal\opendevx_organisation\Utility;

use Drupal\opendevx_taxonomy\Utility\TaxonomyUtility;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

class OrganisationUtility {

  /**
   * Prepare organisation image.
   *
   * @param mixed $data
   *    Organisation data.
   *
   * @return mixed
   *    Organisation Image.
   */
  public static function generateOrganisationImage($data) {
    $url = "";
    if ($data) {
      $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('thumbnail');
      $url = $style->buildUrl($data);
    }

    return $url;
  }

  /**
   * Fetch organisation name.
   *
   * @param mixed $path
   *    Reference id.
   *
   * @return mixed
   *    Organisation.
   */
  public static function getCurrentOrganisation($path) {
    $path = \Drupal::service('path.alias_manager')->getPathByAlias($path);
    if(preg_match('/node\/(\d+)/', $path, $matches)) {
      $node = \Drupal\node\Entity\Node::load($matches[1]);
      $data = $node->toArray();
      $current_org = '';
      if (!empty($data['field_organisation'])) {
        $org_id = $data['field_organisation'][0]['target_id'];
        // Get current organisation name.
        $current_org = TaxonomyUtility::getTaxonomyData((int) $org_id, 'name');
      }

      return $current_org;
    }
  }

  /**
   * Fetch Image URI.
   *
   * @param integer $mid
   *    Media Id.
   *
   * @return string
   *    File Uri.
   */
  public static function getImageUri($mid) {
    $media = Media::load($mid);
    $fid = $media->field_media_image->target_id;
    $file = File::load($fid);
    if ($file) {
      $file_uri = $file->getFileUri();

      return $file_uri;
    }

    return FALSE;
  }
}
