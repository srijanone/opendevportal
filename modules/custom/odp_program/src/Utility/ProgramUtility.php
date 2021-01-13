<?php

namespace Drupal\odp_program\Utility;

use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;

class ProgramUtility {

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
      return $file->getFileUri();
    }

    return FALSE;
  }
}
