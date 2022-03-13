<?php

namespace Drupal\menu_admin_per_menu;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Menu Admin Per Menu Permissions class.
 *
 * Manages getting a list of menus, and generating a list of permissions per
 * menu.
 *
 * @ingroup menu_admin_per_menu
 */
class MenuAdminPerMenuPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of existing menus on site.
   *
   * @return array
   *   Array of existing menus on the site.
   */
  protected function getExistingMenus() {
    return menu_ui_get_menus();
  }

  /**
   * Returns an array of menu_admin_per_menu permissions.
   *
   * @return array
   *   Array of permissions associated with menus.
   */
  public function permissions() {
    $permissions = [];
    $menus = $this->getExistingMenus();
    foreach ($menus as $name => $title) {
      $permission = 'administer ' . $name . ' menu items';
      $permissions[$permission] = [
        'title' => $this->t('Administer <em>@menu</em> menu items', ['@menu' => $title]),
      ];
    }
    return $permissions;
  }

}
