<?php

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function opendevx_menu_token_info() {
  $info = [];

  $info['types']['program'] = [
    'name' => t('Program Tokens'),
    'description' => t('Custom tokens to solve use-case problems for the our website.'),
  ];

  $info['tokens']['program']['program_id'] = [
    'name' => 'Program Id',
    'description' => t('This token will return the program id. Useful for setting URL alias pattern.')
  ];

  $info['tokens']['program']['program_name'] = [
    'name' => 'Program',
    'description' => t('This token will return the program title. Useful for setting URL alias pattern.')
  ];

  return $info;
}

/**
 * Implements hook_tokens().
 */
function opendevx_menu_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) : array {
  $replacements = [];
  // Call the user custom service to get the user's program details.
  $program_service = \Drupal::service('opendevx_user.organisation');
  $program_id = $program_service->getOrgId() ?: 0;
  if ($type == 'program') {
    $bubbleable_metadata->setCacheMaxAge(0);
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'program_id':
          $replacements[$original] = $program_id;
          break;

        case 'program_name':
          $replacements[$original] = $program_service->getOrganisation()[$program_id];
          break;

        default:
          break;
      }
    }
  }

  return $replacements;
}
