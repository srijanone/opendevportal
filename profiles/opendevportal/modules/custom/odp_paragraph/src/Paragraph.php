<?php

namespace Drupal\odp_paragraph;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;

/**
 * ParagraphData class to handle node functionalities.
 */
class Paragraph {

  use LoggerChannelTrait;

  /**
   * @var int $paragraphId
   */
  protected $paragraphId;
  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Set Paragraph id
   *
   * @param mixed $paragraph_id
   *   Paragraph Id.
   */
  public function setParagraphId($paragraph_id) {
    $this->paragraphId = $paragraph_id;
    if (is_int($paragraph_id)) {
      $this->paragraphId = [$paragraph_id];
    }

    return $this;
  }

  /**
   * Get Paragraph Id.
   */
  public function getParagraphId() {
    return $this->paragraphId;
  }

  /**
   * Method to get product attributes data.
   */
  public function getProductAttributesData() {
    try {
      $query = $this->connection->select('paragraphs_item_field_data', 'pifd');
      $query->addField('pfv', 'field_value_value', 'p_value');
      $query->addField('pfk', 'field_key_value', 'p_key');
      $query->addJoin('left', 'paragraph__field_key',
      'pfk', 'pfk.entity_id = pifd.id');
      $query->addJoin('left', 'paragraph__field_value', 'pfv',
      'pfv.entity_id = pifd.id');
      $query->condition('pifd.type', 'product_attributes', '=');
      $query->condition('pifd.id', $this->paragraphId, 'IN');
      $result = $query->distinct()->execute()->fetchAll();

      $product_attributes = [];
      if (!empty($result)) {
        foreach ($result as $value) {
          $product_attributes[$value->p_key] = $value->p_value;
        }
      }

      return $product_attributes;
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('developer-portal-paragraph');
      $logger->error($e->getMessage());
    }
  }

}
