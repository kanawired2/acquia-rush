<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\layoutcomponents\Api\General as General;

/**
 * Class Text.
 *
 * Provide text form element.
 */
class Checkbox {

  use General;

  /**
   * Provide the processed element.
   *
   * @param array $data
   *   The default values.
   */
  public function normal(array $data) {

    // Define the attributes.
    $data['attributes']['lc']['input'] = 'checkbox';

    // Default values.
    $element = [
      '#type' => 'checkbox',
    ];

    if (isset($data['states'])) {
      $element['#states'] = [
        'visible' => [
          ':input[name="' . $data['states'] . '"]' => ['checked' => TRUE],
        ],
        'invisible' => [
          ':input[name="' . $data['states'] . '"]' => ['checked' => FALSE],
        ],
      ];
    }

    // Return new element.
    return $this->getElement($data, $element);
  }

}
