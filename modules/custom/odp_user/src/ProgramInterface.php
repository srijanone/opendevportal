<?php

namespace Drupal\odp_user;

/**
 * Interface defining different constants.
 */
interface ProgramInterface {

  /**
   * Product manager roles of all group types.
   */
  const ADMIN_ROLES = [
    'administrator',
    'admin',
  ];

  /**
   * Product manager roles of all group types.
   */
  const PM_ROLES = [
    'private-product_manager',
    'protected-product_manager',
    'public-product_manager',
  ];

  /**
   * Developer roles of all group types.
   */
  const DEV_ROLES = [
    'private-developer',
    'protected-developer',
    'public-developer',
  ];

  /**
   * Membership Type of all group types.
   */
  const MEMBERSHIP_TYPE = [
    'private-group_membership',
    'protected-group_membership',
    'public-group_membership',
  ];

}
