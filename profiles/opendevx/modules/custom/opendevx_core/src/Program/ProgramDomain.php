<?php

namespace Drupal\opendevx_core\Program;

use Drupal\Core\TempStore\PrivateTempStoreFactory;

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
   * Pass the dependency to the object constructor.
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store
    ) {
    $this->tempstore = $temp_store;
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
    if ($this->getProgramDomainId() > 0){
      $program = \Drupal::entityTypeManager()->getStorage('group')->load($this->getProgramDomainId());
    }
    if (empty($program)) {
      $program = \Drupal::routeMatch()->getParameter('group');
    }
    return $program;

  }

}
