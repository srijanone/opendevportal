<?php

namespace Drupal\opendevx_core\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opendevx_core\Program\ProgramDomainInterface;

/**
 * Filters program on current domain.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("program_id_filter")
 */
class ProgramIdFilterPlugin extends FilterPluginBase {

  /**
   * Object ProgramDomain.
   *
   * @var Drupal\opendevx_core\Program\ProgramDomainInterface
   */
  protected $programDomain;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(ProgramDomainInterface $program_domain) {
    $this->programDomain = $program_domain;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $container->get('opendevx_core.program_domain'),
    
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if ($program_id = $this->programDomain->getProgramDomainId()) {
      $this->query->addWhere('and', 'groups_field_data.id', $program_id);
    }
  }

}
