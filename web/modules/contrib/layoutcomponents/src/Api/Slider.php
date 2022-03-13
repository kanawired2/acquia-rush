<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\layoutcomponents\Api\General as General;

/**
 * Class Slider.
 *
 * Provide slider form element.
 */
class Slider {

  use General;

  /**
   * Provide the processed element.
   *
   * @param array $data
   *   The default values.
   */
  public function sliderWidget(array $data) {
    $data['attributes']['lc']['input'] = 'select';
    $data['attributes']['class'] = [
      'sliderwidget-value-field',
      'form-number',
      'form-control',
      'lc_inline-slider',
    ];

    // Default values.
    $element = [
      '#type' => 'slider',
      '#input_title' => '',
      '#orientation' => 'horizontal',
      '#min' => $data['min'],
      '#max' => $data['max'],
      '#display_inputs' => FALSE,
      '#display_values' => TRUE,
      '#display_values_format' => '%{value}% px',
      '#slider_style' => 'lc-range-slide',
      '#size' => 1,
    ];

    // Return new element.
    return $this->getElement($data, $element);
  }

}
