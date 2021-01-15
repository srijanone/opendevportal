<?php

namespace Drupal\odp_domain\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\odp_domain\Program\ProgramDomainInterface;

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
   * @var Drupal\odp_domain\Program\ProgramDomainInterface
   */
  protected $programDomain;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProgramDomainInterface $program_domain) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->programDomain = $program_domain;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('odp_domain.program_domain'),
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
    if ($program_id = $this->programDomain->getProgramDomainId()) {
      $this->query->addWhere('and', 'group_content_field_data.gid', $program_id);
    }
  }

}
