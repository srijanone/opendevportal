<?php

namespace Drupal\opendevx_sdk\Utility;

use Drupal\node\NodeInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * SDK Utility class.
 */
class GenerateSdk {

  /**
   * Get all the languages supported for SDK Generation.
   *
   * @return array
   *   The drop down select list options.
   */
  public function getSdkLanguages() {
    $result = [
      '-none' => 'Select',
    ];

    try {
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => 'sdk_languages']);
      foreach ($terms as $term) {
        $result[$term->getName()] = $term->getName();
      }
      return $result;
    }
    catch (\Exception $e) {
      \Drupal::service('opendevx_user.logger')->log(
        ['module' => 'opendevx_sdk', 'message' => $e->getMessage()]
      );
    }

    return $result;
  }

  /**
   * Get the API spec path from the current open node.
   *
   * @param int $nid
   *   The node id.
   *
   * @return string
   *   The url of the API spec file.
   */
  public function getApiSpecFromNode($nid) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    if ($node instanceof NodeInterface) {
      $file_id = $node->get('field_document')->getValue()[0]['target_id'];
      if (!empty($file_id)) {
        $file = File::load($file_id);
        return Url::fromUri(file_create_url($file->getFileUri()))->toString();
      }
    }
  }

  /**
   * POST request to the openapi-generator API.
   *
   * @param string $url
   *   The URL of the openapi-generator API.
   * @param string $lang
   *   The Language of the openapi-generator API.
   * @param string $spec_path
   *   The path of the API spec.
   *
   * @return string
   *   The path of the downloadable link.
   */
  public function sdkGenerateRequest($url, $lang, $spec_path) {
    try {
      $response = \Drupal::httpClient()
        ->post($url . '/api/gen/clients/' . $lang, [
          'body' => json_encode(["openAPIUrl" => $spec_path]),
          'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => '*/*',
          ],
        ]);
      $json_string = (string) $response->getBody();
      $json_obj = json_decode($json_string);
      return $json_obj->link;
    }
    catch (ClientException $e) {
      \Drupal::service('opendevx_user.logger')->log(
        ['module' => 'opendevx_sdk', 'message' => $e->getMessage()]
      );
    }
  }

}
