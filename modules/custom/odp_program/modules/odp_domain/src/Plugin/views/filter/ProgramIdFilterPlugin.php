<?php

namespace Drupal\odp_domain\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\odp_domain\Program\ProgramDomainInterface;

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
   * @var Drupal\odp_domain\Program\ProgramDomainInterface
   */
  protected $programDomain;

  /**
   * Pass the dependency to the object constructor.
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
    if ($program_id = $this->programDomain->getProgramDomainId()) {
      $this->query->addWhere('and', 'groups_field_data.id', $program_id);
    }
  }

}
