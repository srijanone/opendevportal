<?php

namespace Drupal\odp_api_documents;

/**
 * Interface defining constants and function for APIs.
 */
interface ApiInterface {

  /**
   * API Options.
   */
  const API_OPTIONS = [
    'api-document' => 'api_document',
    'documents' => 'document_overview',
    'faqs' => 'faq',
    'landing-page' => 'listing_pages',
    'products' => 'api_product',
    'use-cases' => 'use_cases',
  ];

}
