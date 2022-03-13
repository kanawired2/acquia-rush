<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\layoutcomponents\Api\General as General;

/**
 * Class Text.
 *
 * Provide text form element.
 */
class Text {

  use General;

  /**
   * Provide the processed element text.
   *
   * @param array $data
   *   The default values.
   * @param string $type
   *   The new type.
   */
  public function plainText(array $data, $type = '') {
    $data['attributes']['lc']['input'] = 'ckeditor';
    $element = [];
    // Default values.
    if (empty($type)) {
      $data['attributes']['lc']['input'] = 'text';
      $element = [
        '#type' => 'textfield',
      ];
    }
    return $this->getElement($data, $element);
  }

  /**
   * Provide the processed element textarea.
   *
   * @param array $data
   *   The default values.
   * @param string $type
   *   The new type.
   */
  public function plainTextArea(array $data) {
    $element = [
      '#type' => 'textarea',
      '#rows' => $data['rows'],
      '#cols' => $data['cols'],
    ];
    return $this->getElement($data, $element);
  }

}
