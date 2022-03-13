<?php

namespace Drupal\layoutcomponents\Access;

use Drupal\Core\Session\AccountProxy;

/**
 * Methods to help with sthe access control of LC elements.
 */

trait LcAccessHelperTrait {

  /**
   * Get the current entity.
   *
   * @return \stdClass
   *   The entity.
   */
  protected function getCurrentEntity() {
    $parameters = \Drupal::routeMatch()->getParameters();
    $type = $parameters->get('entity_type_id');
    return $parameters->get($type);
  }

  /**
   * Check if the user is allowed.
   *
   * @param \Drupal\Core\Session\AccountProxy $account
   *   The section storage object.
   * @param  string $permission
   *   The permission.
   *
   * @return bool
   *   TRUE|FALSE.
   */
  protected function getAccess(AccountProxy $account, $permission) {
    if ($account->hasPermission($permission) || in_array('administrator', $account->getRoles())) {
      return TRUE;
    }
    return FALSE;
  }
}
