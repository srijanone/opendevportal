<?php

namespace Drupal\opendevx_node;

/**
 * Interface defining node helper.
 */
interface ContentInterface {

  /**
   * Content types.
   */
  const CONTENT_TYPES = [
    'api_document',
    'apps',
    'article',
    'assets',
    'document_overview',
    'events',
    'faq',
    'forum',
    'issues',
    'resources',
    'solutions',
    'tutorials',
    'use_cases',
  ];

}
