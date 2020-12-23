<?php

namespace Drupal\odp_core\Program;

/**
 * Interface defining program domain helper service.
 */
interface ProgramDomainInterface {

  /**
   * Program Id Key.
   */
  const STORAGE_PROGRAM_KEY = 'program_id';

  /**
   * Set the Program Domain id in temp storage.
   */
  public function setProgramDomainId($program_id = NULL);

  /**
   * Return Program Domain id from temp storage .
   *
   * @return int
   *   URL to redirect to on success or NULL otherwise.
   */
  public function getProgramDomainId();

  /**
   * Delete Program Domain id from temp storage .
   *
   * @return bool
   *   return true if delete.
   */
  public function deleteProgramDomainId();

}
