<?php

namespace Drupal\odp_breadcrumb;

/**
 * Interface defining different constants.
 */
interface BreadcrumbInterface {

  /**
   * Custom Breadcrum view id.
   */
  const BREADCRUMB_VIEW_ID = [
    'programs',
    'proxies_listing',
    'dashboard_listing',
    'organisation_users',
    'content',
    'block_content',
    'contents_list',
    'group_members',
    'forum',
  ];

  /**
   * Site-Admin menu machine name.
   */
  const SITEADMIN_MENU_NAME = 'dashboard-menu';

  /**
   * Product Manager menu machine name.
   */
  const PRODUCT_MANAGER_MENU_NAME = 'product-management';

  /**
   * Developer menu machine name.
   */
  const DEVELOPER_MENU_NAME = 'developer-menu';

  /**
   * Content type name mapping.
   */
  const CONTENT_TYPE_NAME_MAPPING = [
    'apps' => 'Applications',
    'article' => 'Blogs',
    'assets' => 'Media',
    'document_overview' => 'Pages',
    'events' => 'Events',
    'faq' => 'FAQs',
    'forum' => 'Forum',
    'issues' => 'Issues',
    'resources' => 'Downloads',
    'solutions' => 'Solutions',
    'tutorials' => 'Tutorials',
    'use_cases' => 'Use Cases',
    'api_document' => 'API References',
  ];

}
