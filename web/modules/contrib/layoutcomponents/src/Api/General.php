<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provide the form elements for auto preview in LC editor.
 *
 * @ingroup lc
 */
trait General {

  use StringTranslationTrait;

  /**
   * Provide the processed element.
   *
   * @param array $data
   *   The default values.
   * @param array $element
   *   The default element.
   */
  public function getElement(array $data, array $element) {
    // Alter class.
    $class = 'lc-inline_' . $data['id'];

    if (!empty($data['class'])) {
      $class .= '-' . $data['class'];
    }

    // Serialize the LC attributes.
    $data['attributes']['input'] = (isset($data['attributes']['lc']['input'])) ? $data['attributes']['lc']['input'] : '';
    $data['attributes']['lc']['id'] = 'lc-inline_' . $data['id'];
    $data['attributes']['lc']['class'] = $class;
    $data['attributes']['lc'] = Json::encode($data['attributes']['lc']);
    $data['attributes']['class'][] = $class;

    // Generate the new element.
    $new_element = [
      '#default_value' => $data['default_value'],
      '#title' => $this->getLcTitle($data),
      '#attributes' => $data['attributes'],
    ];

    if (array_key_exists('#access', $data)) {
      $new_element['#access'] = $data['#access'];
    }

    // Merge with old element.
    $element = array_merge($new_element, $element);

    return $element;
  }

  /**
   * Provide the processed element.
   *
   * @param array $data
   *   The default values.
   */
  public function getLcTitle(array $data) {
    $data['description'] = str_replace('<br />The maximum number of media items have been selected.', '', $data['description']);
    $data['description'] = str_replace('<br />One media item remaining.', '', $data['description']);
    if (strpos($data['title'], 'lc-lateral-title')) {
      return $data['title'];
    }
    return '<span class="lc-lateral-title">' . $data['title'] . '</span>' . '<span class="lc-lateral-info" title="' . $data['description'] . '"/>';
  }

}
