CONTENTS OF THIS FILE
---------------------
 * Introduction
 * Configuration

INTRODUCTION
------------
Module provides ability:

 * Redirect user (to specific URL) on Log in
 * Set specific redirect URL for each role
 * Set roles redirect priority
 * Use Tokens in Redirect URL value
 * CAS integration

 Roles order in list (configuration form) is their priorities:
 higher in list - higher priority. For example: You set roles ordering as:

 + Admin
 + Manager
 + Authenticated

 It means that when some user log in (in case of "Login redirect" table,
 configuration form) or log out (in case of "Logout redirect" table,
 configuration form) module will check:

 Does this user have Admin role?

  * Yes and Redirect URL is not empty: Redirect to related URL
  * No or Redirect URL is empty:

 Does this user have Manager role?

  * Yes and Redirect URL is not empty: Redirect to related URL
  * No or Redirect URL is empty:

 Does this user have Authenticated role?

  * Yes and Redirect URL is not empty: Redirect to related URL
  * No or Redirect URL is empty: Use default Drupal action

CONFIGURATION
-------------
* In menu go to: Configuration -> System -> Login and Logout Redirect per role
  (or /admin/people/opendevx/login-redirect)

* Set "Login redirect" table "Redirect URL" values and roles priority
  (order in table) to setup redirect user on Log in action. Or leave
  "Redirect URL" values empty if you don't need redirect on user Log in.

* Click "Save configuration" button.
