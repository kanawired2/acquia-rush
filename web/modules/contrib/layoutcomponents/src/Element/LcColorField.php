<?php

namespace Drupal\layoutcomponents\Element;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Provide a color box form element.
 *
 * Usage example:
 * @code
 * $form['color'] = [
 *   '#type' => 'color_field_element_box',
 *   '#title' => t('Color'),
 *   '#color_options' => [rgb(255,255,255), rgb(0,0,0)],
 *   '#required' => TRUE,
 *   '#default_value' => [
 *     'color' => rgb(255,255,255),
 *     'opacity' => 0.5,
 *   ],
 * ];
 * @endcode
 *
 * @FormElement("color_field_element_box")
 */
class LcColorField extends FormElement {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processColorFieldElementBox'],
      ],
    ];
  }

  /**
   * Create form element structure for color boxes.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $form
   *   The complete form structure.
   *
   * @return array
   *   The form element.
   */
  public static function processColorFieldElementBox(array &$element, FormStateInterface $form_state, array &$form): array {
    $element['#uid'] = Html::getUniqueId($element['#name']);

    // Create fieldset of color and opacity.
    $element['settings'] = [
      '#type' => 'fieldset',
      '#title' => $element['#title'],
    ];
    $element['settings']['color'] = [
      '#type' => 'textfield',
      '#required' => $element['#required'],
      '#default_value' => $element['#default_value']['color'],
      '#suffix' => "<div class='color-field-widget-box-form' id='" . $element['#uid'] . "'></div>" . t('<p><a class="lc-url-settings" href=":url">Color Settings</a></p>', [':url' => Url::fromRoute('layoutcomponents.colors_settings')->toString()]),
    ];
    $element['settings']['opacity'] = [
      '#title' => '<span class="lc-lateral-title">' . t('Opacity') . '</span>' . '<span class="lc-lateral-info" title="' . t('Set the opacity') . '"/>',
      '#type' => 'number',
      '#min' => 0,
      '#max' => 1,
      '#step' => 0.01,
      '#required' => $element['#required'],
      '#default_value' => $element['#default_value']['opacity'],
      '#placeholder' => 1.0,
    ];

    // Set Drupal settings.
    $settings[$element['#uid']] = [
      'required' => $element['#required'],
    ];

    // Add allowed colors for color boxes.
    foreach ($element['#color_options'] as $color) {
      $settings[$element['#uid']]['palette'][] = $color;
    }

    if (isset($element['#attributes']) && is_array($element['#attributes'])) {
      $element['settings']['color']['#attributes'] = NestedArray::mergeDeepArray([$element['#attributes']], TRUE);
      if (array_key_exists('opacity', $element['#attributes'])) {
        $element['settings']['opacity']['#attributes']['lc'] = Json::encode(NestedArray::mergeDeepArray([$element['#attributes']['opacity']['lc']], TRUE));
        $element['settings']['opacity']['#attributes']['class'][] = $element['#attributes']['opacity']['class'];
        $element['settings']['opacity']['#attributes']['input'] = $element['#attributes']['opacity']['input'];
      }
    }

    // Attach color_field module's library.
    $element['#attached']['library'][] = 'color_field/color-field-widget-box';
    $element['#attached']['drupalSettings']['color_field']['color_field_widget_box']['settings'] = $settings;

    return $element;
  }

}
