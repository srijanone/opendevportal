<?php

namespace Drupal\opendevx_core\Plugin\views\filter;

use Drupal\views\Views;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\opendevx_core\Program\ProgramDomainInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filters nodes on current program domian.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("program_domain_filter")
 */
class ProgramDomainFilterPlugin extends FilterPluginBase {

   /**
   * Object ProgramDomain.
   *
   * @var Drupal\opendevx_core\Program\ProgramDomainInterface
   */
  protected $programDomain;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct($plugin_id, $plugin_definition, ProgramDomainInterface $program_domain) {
    $this->programDomain = $program_domain;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $container->get('opendevx_core.program_domain')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {

    $configuration = [
      'table' => 'group_content_field_data',
      'field' => 'entity_id',
      'left_table' => 'node_field_data',
      'left_field' => 'nid',
      'operator' => '=',
    ];
    $join = Views::pluginManager('join')->createInstance('standard', $configuration);
    $this->query->addRelationship('group_content_field_data', $join, 'node_field_data');
    if ($program_id = $this->programDomain ->getProgramDomainId()) {
      $this->query->addWhere('and', 'group_content_field_data.gid', $program_id);
    }
  }

}
