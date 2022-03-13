<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\layoutcomponents\Api\General as General;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Class Media.
 *
 * Provide media form element.
 */
class Media {

  use General;
  use DependencySerializationTrait;

  /**
   * Provide the processed element.
   *
   * @param array $data
   *   The default values.
   * @param string $type
   *   The new type.
   * @param string $input
   *   The input type.
   */
  public function mediaLibrary(array $data, $type = '', $input = '') {
    // Define the attributes.
    $data['attributes']['lc']['input'] = (empty($input)) ? 'image' : $input;

    // Default values.
    $element = [
      '#type' => (empty($type)) ? 'media_library' : $type,
      '#allowed_bundles' => $data['allowed_bundles'],
    ];

    // Return new element.
    return $this->getElement($data, $element);
  }

}
