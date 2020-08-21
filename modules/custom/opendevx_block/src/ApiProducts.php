<?php

namespace Drupal\opendevx_block;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\opendevx_block\Utility\BlockUtility;

/**
 * Class to extend and provide the features of Api products.
 */
class ApiProducts {

  use LoggerChannelTrait;

  /**
   * The database connection instance.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The account instance that represents current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The product instance.
   *
   * @var int
   */
  protected $productId;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    Connection $connection,
    AccountInterface $account) {
    $this->account = $account;
    $this->connection = $connection;
  }

  /**
   * Set the product id.
   *
   * @param int $productId
   *   Product id.
   */
  public function setProductId($productId) {
    $this->productId = $productId;
    return $this;
  }

  /**
   * Get the product id.
   *
   * @return int
   *   Product ID.
   */
  public function getProductId() {
    return $this->productId;
  }

  /**
   * Function to get product reference type.
   *
   * @param mixed $list
   *   Optional.
   *
   * @return string
   *   String value of node data.
   */
  public function getProductReferenceType($list = []) {
    try {
      $this->connection->query("SET SQL_MODE=''");
      $query = $this->connection->select('node_field_data', 'nfd');
      $query->fields('nfd', ['title', 'type']);
      $query->addJoin('left', 'node__field_api_product', 'nfp',
      'nfp.field_api_product_target_id = nfd.nid');
      $query->addJoin('left', 'node__field_api_specifications', 'nfa',
      'nfa.entity_id = nfd.nid');
      $query->addExpression('GROUP_CONCAT(DISTINCT nfp.bundle)', 'child');
      $query->addExpression('GROUP_CONCAT(DISTINCT nfa.bundle)', 'api');
      $query->addField('nfa', 'field_api_specifications_target_id', 'api_id');
      $query->condition('nfd.nid', $this->productId, '=');
      $query->condition('nfd.status', 1, '=');
      $query->groupBy('nfd.title');
      $result = $query->distinct()->execute()->fetchAssoc();
      if (empty($list)) {
        return $result;
      }
      $output = BlockUtility::prepareNavigationBlock($result, $this->productId, $list);

      return $output;
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('developer-portal-block');
      $logger->error($e->getMessage());
    }
  }

  /**
   * Function to get title of product node.
   *
   * @return string
   *   title of node id.
   */
  public function getTitle() {
    try {
      $query = $this->connection->select('node_field_data', 'nfd');
      $query->fields('nfd', ['title']);
      $query->condition('nfd.nid', $this->productId, '=');
      $query->condition('nfd.status', 1, '=');
      $query->condition('nfd.type', 'api_product', '=');
      $result = $query->distinct()->execute()->fetchAssoc();

      return $result;
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('developer-portal-block');
      $logger->error($e->getMessage());
    }
  }

  /**
   * Function to get first index sub product path.
   *
   * @param mixed $list
   *   Block navigation data.
   *
   * @return string
   *   title of node id.
   */
  public function getFirstIndexProductContents($list) {
    $get_navigation = $this->getProductReferenceType($list);
    $redirection = array_column(array_values($get_navigation)[0], 'childPath');

    return $redirection[0];
  }

}
