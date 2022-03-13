<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\Core\Config\ConfigFactory;
use Drupal\layoutcomponents\Api\General as General;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Class Color.
 *
 * Provide color form element.
 */
class Color {

  use General;
  use DependencySerializationTrait;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Provide the processed element.
   *
   * @param array $data
   *   The default values.
   * @param string $type
   *   The new type.
   */
  public function colorPicker(array $data, $type = 'color_field_element_box') {
    // Define the attributes.
    $data['attributes']['lc']['input'] = 'color';
    $data['attributes']['lc']['type'] = 'style';
    $data['attributes']['lc']['depend']['opacity']['color'] = 'lc-inline_' . $data['id'] . '-' . $data['class'];
    $data['attributes']['opacity'] = [
      'input' => 'opacity',
      'lc' => [
        'id' => 'lc-inline_' . $data['id'],
        'type' => 'style',
        'style' => 'opacity',
        'depend' => $data['attributes']['lc']['depend']['opacity'],
      ],
      'class' => 'lc-inline_' . $data['id'] . '-' . $data['class'] . '-opacity',
    ];

    unset($data['attributes']['lc']['depend']['opacity']);

    // Default values.
    $element = [
      '#type' => $type,
    ];

    // Return new element.
    $element = $this->getElement($data, $element);

    if ($type == 'color_field_element_box') {
      // Get LC colors.
      $colors = $this->configFactory->getEditable('layoutcomponents.colors')->getRawData()['editor_colors_list'];
      $colors = str_replace(' ', '', $colors);
      $colors = explode(',', $colors);

      // Set the colors.
      $element['#color_options'] = $colors;
    }

    return $element;
  }

}
