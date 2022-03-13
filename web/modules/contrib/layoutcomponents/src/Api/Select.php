<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\layoutcomponents\Api\General as General;

/**
 * Class Select.
 *
 * Provide select form element.
 */
class Select {

  use General;

  /**
   * Provide a select form element processed.
   *
   * @param array $data
   *   The complete data.
   */
  public function normal(array $data) {
    $data['attributes']['lc']['input'] = 'select';
    $data['attributes']['class'] = ['form-select', 'form-control'];

    // Default values.
    $element = [
      '#type' => 'select',
      '#options' => $data['options'],
    ];

    // Return new element.
    return $this->getElement($data, $element);
  }

}
