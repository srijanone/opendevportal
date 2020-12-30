<?php

namespace Drupal\odp_node;

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

  /**
   * Form IDs.
   */
  const FORM_IDS = [
    'node_document_overview_form',
    'node_document_overview_edit_form',
    'node_api_document_form',
    'node_api_document_edit_form',
    'node_api_product_form',
    'node_api_product_edit_form',
    'node_apps_form',
    'node_apps_edit_form',
    'node_article_form',
    'node_article_edit_form',
    'node_assets_form',
    'node_assets_edit_form',
    'node_events_form',
    'node_events_edit_form',
    'node_forum_form',
    'node_forum_edit_form',
    'node_issues_form',
    'node_issues_edit_form',
    'node_listing_pages_form',
    'node_listing_pages_edit_form',
    'node_resources_form',
    'node_resources_edit_form',
    'node_solutions_form',
    'node_solutions_edit_form',
    'node_tutorials_form',
    'node_tutorials_edit_form',
    'node_use_cases_form',
    'node_use_cases_edit_form',
  ];

}
