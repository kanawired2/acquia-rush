<?php

namespace Drupal\layoutcomponents;

use Drupal\Component\Serialization\Json;

/**
 * Methods to help LC dialogs.
 */
trait LcDialogHelperTrait {

  /**
   * Gets revision IDs for layout sections.
   *
   * @return false|string
   *   The array with dialog options.
   */
  protected function dialogOptions() {
    return Json::encode([
      'width' => $this->configFactory()->getEditable('layoutcomponents.general')->get('width'),
      'dialogClass' => [
        'ui-dialog-off-canvas',
        'ui-dialog-position-side',
        'ui-resizable',
        'lc-ui-dialog',
      ],
    ]);
  }

  /**
   * Get the confgiFactory object.
   *
   * @return \Drupal\Core\Config\ConfigFactory
   *   The configFactory object.
   */
  private function configFactory() {
    return \Drupal::service('config.factory');
  }

}
