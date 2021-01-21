<?php

namespace Drupal\odp_program\Utility;

/**
 * ProgramUtility class to handle program functionalities.
 */
class ProgramUtility {

  /**
   * Prepare organisation image.
   *
   * @param mixed $data
   *   Organisation data.
   *
   * @return mixed
   *   Organisation Image.
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
   * Fetch Image URI.
   *
   * @param int $mid
   *   Media Id.
   *
   * @return string
   *   File Uri.
   */
  public static function getImageUri($mid) {
    $media = \Drupal::entityTypeManager()->getStorage('media')->load($mid);
    $fid = $media->field_media_image->target_id;
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($fid);
    if ($file) {
      return $file->getFileUri();
    }

    return FALSE;
  }

}
