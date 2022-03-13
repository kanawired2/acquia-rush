<?php

namespace Drupal\layoutcomponents\Api;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\layoutcomponents\Api\Text as Text;
use Drupal\layoutcomponents\Api\Slider as Slider;
use Drupal\layoutcomponents\Api\Media as Media;
use Drupal\layoutcomponents\Api\Select as Select;
use Drupal\layoutcomponents\Api\Color as Color;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Component.
 *
 * Provide form elements.
 */
class Component implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Layoutcomponents manager Text.
   *
   * @var \Drupal\layoutcomponents\Api\Text
   */
  protected $lcApiText;

  /**
   * Layoutcomponents manager Slider.
   *
   * @var \Drupal\layoutcomponents\Api\Slider
   */
  protected $lcApiSlider;

  /**
   * Layoutcomponents manager Media.
   *
   * @var \Drupal\layoutcomponents\Api\Media
   */
  protected $lcApiMedia;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\Api\Select
   */
  protected $lcApiSelect;

  /**
   * Layoutcomponents manager Color.
   *
   * @var \Drupal\layoutcomponents\Api\Color
   */
  protected $lcApiColor;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->lcApiText = new Text();
    $this->lcApiSlider = new Slider();
    $this->lcApiMedia = new Media();
    $this->lcApiSelect = new Select();
    $this->lcApiColor = new Color($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Provide the processed element.
   *
   * @param array $data
   *   The complete data.
   * @param array $element
   *   The element configuration.
   */
  public function getComponentElement(array $data, array $element) {
    $lc = $data;

    if (empty($lc)) {
      return;
    }

    // Return a clean element.
    if (isset($lc['no_lc']) && $lc['no_lc'] == TRUE) {
      $element['#title'] = $this->lcApiColor->getLcTitle(
        [
          'title' => (isset($element['#title'])) ? $element['#title'] : '',
          'description' => (isset($element['#description'])) ? $element['#description'] : '',
        ]
      );
      unset($element['#description']);
      $new_element = $element;
      return array_merge($element, $new_element);
    }

    $data['title'] = (isset($element['#title'])) ? $element['#title'] : '';
    $data['description'] = (isset($element['#description'])) ? $element['#description'] : '';
    $data['default_value'] = (isset($element['#default_value'])) ? $element['#default_value'] : '';
    $data['class'] = (isset($lc['class'])) ? $lc['class'] : '';

    $data['attributes'] = (isset($element['#attributes'])) ? $element['#attributes'] : [];
    $data['attributes']['lc'] = $lc;
    $data['attributes']['edit'] = 'layout-builder-configure-block';
    unset($element['#description']);

    switch ($data['attributes']['lc']['element']) {
      case 'text':
        $format = ($data['attributes']['lc']['input'] == 'ckeditor') ? 'text_format' : '';
        $new_element = $this->lcApiText->plainText($data, $format);
        break;

      case 'url':
        $element['#title'] = $this->lcApiColor->getLcTitle(
          [
            'title' => $data['title'],
            'description' => $data['description'],
          ]
        );
        unset($element['uri']['#title']);
        $new_element = $element;
        break;

      case 'slider':
        $data['min'] = $element['#min'];
        $data['max'] = $element['#max'];
        $new_element = $this->lcApiSlider->sliderWidget($data);
        break;

      case 'media':
        $data['allowed_bundles'] = $element['#target_bundles'];
        // Get new element.
        $new_element = $this->lcApiMedia->mediaLibrary($data, $element['#type'], $data['attributes']['lc']['input']);

        // Apply new attributes to media input.
        $element['selection'][0]['target_id']['#attributes']['lc-media'] = 'lc-inline_' . $data['id'];
        break;

      case 'select':
        $data['options'] = $element['#options'];
        $new_element = $this->lcApiSelect->normal($data);
        break;

      case 'color':
        // Get new element.
        $new_element = $this->lcApiColor->colorPicker($data, $element['#type']);

        // Set the titles.
        $element['color']['#title'] = $this->lcApiColor->getLcTitle(
          [
            'title' => $element['color']['#title'],
            'description' => $element['color']['#description'],
          ]
        );

        $element['opacity']['#title'] = $this->lcApiColor->getLcTitle(
          [
            'title' => $this->t('Opacity'),
            'description' => $this->t('Set the opacity'),
          ]
        );

        // Set LC attributes.
        $element['color']['#attributes'] = [
          'edit' => 'layout-builder-configure-block',
          'input' => 'color',
          'lc' => $new_element['#attributes']['lc'],
          'class' => $new_element['#attributes']['class'],
        ];

        $element['opacity']['#attributes'] = [
          'edit' => 'layout-builder-configure-block',
          'input' => 'opacity',
          'lc' => Json::encode($new_element['#attributes']['opacity']['lc']),
          'class' => $new_element['#attributes']['class'][0] . '-opacity',
        ];

        // Remove old attributes.
        unset($new_element['#attributes']);
        unset($element['color']['#description']);
        unset($element['opacity']['#title']);
        break;

    }

    return array_merge($element, $new_element);
  }

}
