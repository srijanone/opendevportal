<?php

namespace Drupal\odp_core\Program;

use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Program Domain .
 */
class ProgramDomain implements ProgramDomainInterface {

  /**
   * Programs.
   *
   * @var mixed
   */
  protected $tempstore;

  /**
   * Object EntityTypeManager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store,
    EntityTypeManagerInterface $entity_type_manager,
    CurrentRouteMatch $route_match
    ) {
    $this->tempstore = $temp_store;
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * Set Program id.
   *
   * @param int $program_id
   *   Program id.
   */
  public function setProgramDomainId($program_id = NULL) {
    return $this->tempstore->get('program_domain_collection')->set(ProgramDomainInterface::STORAGE_PROGRAM_KEY, $program_id);
  }

  /**
   * Get Program id.
   */
  public function getProgramDomainId() {
    return $this->tempstore->get('program_domain_collection')->get(ProgramDomainInterface::STORAGE_PROGRAM_KEY);
  }

  /**
   * Delete Program id.
   */
  public function deleteProgramDomainId() {
    return $this->tempstore->get('program_domain_collection')->delete(ProgramDomainInterface::STORAGE_PROGRAM_KEY);
  }

  /**
   * Get Program by sub domian.
   */
  public function getDomainProgramInfo() {
    $program = [];
    if ($this->getProgramDomainId() > 0) {
      $program = $this->entityTypeManager->getStorage('group')->load($this->getProgramDomainId());
    }
    if (empty($program)) {
      $program = $this->routeMatch->getParameter('group');
    }

    return $program;
  }

}
