<?php

namespace Drupal\odp_voyager;

/**
 * Interface defining voyager helper.
 */
interface VoyagerInterface {

  /**
   * Form Ids.
   */
  const FORM_IDS = ['node_api_document_form', 'node_api_document_edit_form'];

  /**
   * API type.
   */
  const API_TYPE = ['rest', 'graphql'];

}
