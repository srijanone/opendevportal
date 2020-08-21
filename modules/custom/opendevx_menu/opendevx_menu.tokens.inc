<?php

/**
 * @file
 * Inc file for menu tokens.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function opendevx_menu_token_info() {
  $info = [];

  $info['types']['organization'] = [
    'name' => t('Organization Tokens'),
    'description' => t('Custom tokens to solve use-case problems for the our website.'),
  ];

  $info['tokens']['organization']['organization_id'] = [
    'name' => 'Organization',
    'description' => t('This token will return the organization id either using the default taxonomy id, or the organization_hash_value field value on the taxonoomy. Useful for setting URL alias pattern.'),
  ];

  $info['tokens']['organization']['organization_name'] = [
    'name' => 'Organization',
    'description' => t('This token will return the organization title either using the default taxonomy title. Useful for setting URL alias pattern.'),
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function opendevx_menu_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) : array {
  $replacements = [];
  // Call the user custom service to get the user's organization details.
  $organization_details = \Drupal::service('opendevx_user.organisation');
  $org_id = $organization_details->getOrgId();
  if ($type == 'organization' && $org_id) {
    $bubbleable_metadata->setCacheMaxAge(0);
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'organization_id':
          $replacements[$original] = $org_id;
          break;

        case 'organization_name':
          $replacements[$original] = $organization_details->getOrganisation()[$org_id];
          break;

        default:
          break;
      }
    }
  }

  return $replacements;
}
