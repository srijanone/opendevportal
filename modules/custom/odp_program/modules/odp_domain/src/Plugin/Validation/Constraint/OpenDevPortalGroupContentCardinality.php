<?php

namespace Drupal\odp_domain\Plugin\Validation\Constraint;

use Drupal\group\Plugin\Validation\Constraint\GroupContentCardinality;

/**
 * Overrides the cardinality message.
 */
class OpenDevPortalGroupContentCardinality extends GroupContentCardinality {

  /**
   * The message to show when an entity has reached the group cardinality.
   *
   * @var string
   */
  public $groupMessage = '@field: %content is already assigned to';

  /**
   * The message to show when an entity has reached the entity cardinality.
   *
   * @var string
   */
  public $entityMessage = '@field: %content is already assigned to %group program.';

}
