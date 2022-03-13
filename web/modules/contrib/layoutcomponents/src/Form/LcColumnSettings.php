<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layoutcomponents\LcLayoutsManager;

/**
 * Basic fields settings for LayoutComponents.
 */
class LcColumnSettings extends ConfigFormBase {

  /**
   * The Lc manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $layoutManager;

  /**
   * The Lc column settings contruct.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configFactory object.
   * @param \Drupal\layoutcomponents\LcLayoutsManager $layout_manager
   *   The LcLayoutsManager object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LcLayoutsManager $layout_manager) {
    parent::__construct($config_factory);
    $this->layoutManager = $layout_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.layoutcomponents_layouts')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_settings_COLUMN';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'layoutcomponents.COLUMN',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('layoutcomponents.column');
    $colors = $this->configFactory->getEditable('layoutcomponents.colors')->getRawData()['editor_colors_list'];
    $colors = str_replace(' ', '', $colors);
    $colors = explode(',', $colors);

    $form['general'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Provide the default configuration for the columns'),
      'title' => [
        '#type' => 'details',
        '#title' => $this->t('Title'),
        '#group' => 'general',
        'title_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Text'),
          '#default_value' => $config->get('title_text') ?: '',
          '#description' => $this->t('Set the default text for the titles of the columns'),
        ],
      ],
      'title_styles' => [
        '#type' => 'details',
        '#title' => $this->t('Title - Styles'),
        '#group' => 'general',
        'title_color' => [
          '#type' => 'color_field_element_box',
          '#title' => $this->t('Text color'),
          '#color_options' => $colors,
          '#default_value' => [
            'color' => $config->get('title_color')['settings']['color'] ?: '',
            'opacity' => $config->get('title_color')['settings']['opacity'] ?: 1,
          ],
          '#description' => $this->t('Set the default color for the column titles'),
        ],
        'title_type' => [
          '#type' => 'select',
          '#title' => $this->t('Text type'),
          '#options' => $this->layoutManager->getTagOptions(),
          '#default_value' => $config->get('title_type') ?: 'h1',
          '#description' => $this->t('Set the default type for the column titles'),
        ],
        'title_size' => [
          '#type' => 'number',
          '#title' => $this->t('Text size'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('title_size') ?: (int) 0,
          '#description' => $this->t('Set the default size for the column titles'),
        ],
        'title_align' => [
          '#type' => 'select',
          '#title' => $this->t('Text align'),
          '#options' => $this->layoutManager->getColumnTitleAlign(),
          '#default_value' => $config->get('title_align') ?: 'text-left',
          '#description' => $this->t('Set the default align for the column titles'),
        ],
        'title_border' => [
          '#type' => 'select',
          '#title' => $this->t('Border type'),
          '#options' => [
            'none' => 'None',
            'left' => 'Left',
            'top' => 'Top',
            'right' => 'Right',
            'bottom' => 'Bottom',
            'all' => 'All',
          ],
          '#default_value' => $config->get('title_border') ?: 'none',
          '#description' => $this->t('Set the default border type for the column titles'),
        ],
        'title_border_size' => [
          '#type' => 'number',
          '#title' => $this->t('Border size'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('title_border_size') ?: (int) 0,
          '#description' => $this->t('Set the default border size for the column titles'),
        ],
        'title_border_color' => [
          '#type' => 'color_field_element_box',
          '#title' => $this->t('Border color'),
          '#color_options' => $colors,
          '#default_value' => [
            'color' => $config->get('title_border_color')['settings']['color'] ?: '',
            'opacity' => $config->get('title_border_color')['settings']['opacity'] ?: 1,
          ],
          '#description' => $this->t('Set the default border color for the column titles'),
        ],
      ],
      'background' => [
        '#type' => 'details',
        '#title' => $this->t('Background'),
        '#group' => 'general',
        'background_color' => [
          '#type' => 'color_field_element_box',
          '#title' => $this->t('Background color'),
          '#color_options' => $colors,
          '#default_value' => [
            'color' => $config->get('background_color')['settings']['color'] ?: '',
            'opacity' => $config->get('background_color')['settings']['opacity'] ?: 1,
          ],
          '#description' => $this->t('Set the default background color for the columns'),
        ],
      ],
      'border' => [
        '#type' => 'details',
        '#title' => $this->t('Border'),
        '#group' => 'general',
        'border_type' => [
          '#type' => 'select',
          '#title' => $this->t('Border type'),
          '#options' => $this->layoutManager->getColumnBorder(),
          '#default_value' => $config->get('border_type') ?: 'text-left',
          '#description' => $this->t('Set the default border type for columns'),
        ],
        'border_size' => [
          '#type' => 'number',
          '#title' => $this->t('Border size'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('border_size') ?: (int) 0,
          '#description' => $this->t('Set the default border size for the columns'),
        ],
        'border_color' => [
          '#type' => 'color_field_element_box',
          '#title' => $this->t('Border color'),
          '#color_options' => $colors,
          '#default_value' => [
            'color' => $config->get('border_color')['settings']['color'] ?: '',
            'opacity' => $config->get('border_color')['settings']['opacity'] ?: 1,
          ],
          '#description' => $this->t('Set the default border color for the columns'),
        ],
        'border_radius_top_left' => [
          '#type' => 'number',
          '#title' => $this->t('Border radius top-left'),
          '#min' => (int) 0,
          '#max' => (int) 100,
          '#default_value' => $config->get('border_radius_top_left') ?: (int) 0,
          '#description' => $this->t('Set the default border radius top-left for the columns'),
        ],
        'border_radius_top_right' => [
          '#type' => 'number',
          '#title' => $this->t('Border radius top-right'),
          '#min' => (int) 0,
          '#max' => (int) 100,
          '#default_value' => $config->get('border_radius_top_right') ?: (int) 0,
          '#description' => $this->t('Set the default border radius top-right for the columns'),
        ],
        'border_radius_bottom_left' => [
          '#type' => 'number',
          '#title' => $this->t('Border radius bottom-left'),
          '#min' => (int) 0,
          '#max' => (int) 100,
          '#default_value' => $config->get('border_radius_bottom_left') ?: (int) 0,
          '#description' => $this->t('Set the default border bottom top-left for the columns'),
        ],
        'border_radius_bottom_right' => [
          '#type' => 'number',
          '#title' => $this->t('Border radius bottom-right'),
          '#min' => (int) 0,
          '#max' => (int) 100,
          '#default_value' => $config->get('border_radius_bottom_right') ?: (int) 0,
          '#description' => $this->t('Set the default border radius bottom-right for the columns'),
        ],
      ],
      'paddings' => [
        '#type' => 'details',
        '#title' => $this->t('Paddings'),
        '#group' => 'general',
        'remove_paddings' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Remove paddings'),
          '#default_value' => $config->get('remove_paddings') ?: boolval(0),
          '#description' => $this->t('Check this options to remove all paddings of the columns'),
        ],
        'remove_left_padding' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Remove left padding'),
          '#default_value' => $config->get('remove_left_padding') ?: boolval(0),
          '#description' => $this->t('Check this options to remove the left padding of the columns'),
        ],
        'remove_right_padding' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Remove right padding'),
          '#default_value' => $config->get('remove_right_padding') ?: boolval(0),
          '#description' => $this->t('Check this options to remove the right padding of the columns'),
        ],
      ],
      'misc' => [
        '#type' => 'details',
        '#title' => $this->t('Misc'),
        '#group' => 'general',
        'extra_class' => [
          '#type' => 'textfield',
          '#title' => $this->t('Extra class'),
          '#default_value' => $config->get('extra_class') ?: '',
          '#description' => $this->t('Set the default extra class for the columns, ej: myclass1 myclass2'),
        ],
      ],
    ];

    $form['general']['#tree'] = TRUE;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValues()['general']['title'];
    $title_styles = $form_state->getValues()['general']['title_styles'];
    $background = $form_state->getValues()['general']['background'];
    $border = $form_state->getValues()['general']['border'];
    $paddings = $form_state->getValues()['general']['paddings'];
    $misc = $form_state->getValues()['general']['misc'];

    $title_text = $title['title_text'];
    $title_color = $title_styles['title_color'];
    $title_type = $title_styles['title_type'];
    $title_align = $title_styles['title_align'];
    $title_size = $title_styles['title_size'];
    $title_border = $title_styles['title_border'];
    $title_border_color = $title_styles['title_border_color'];
    $title_border_size = $title_styles['title_border_size'];
    $background_color = $background['background_color'];
    $border_type = $border['border_type'];
    $border_size = $border['border_size'];
    $border_color = $border['border_color'];
    $border_radius_top_left = $border['border_radius_top_left'];
    $border_radius_top_right = $border['border_radius_top_right'];
    $border_radius_bottom_left = $border['border_radius_bottom_left'];
    $border_radius_bottom_right = $border['border_radius_bottom_right'];
    $remove_paddings = $paddings['remove_paddings'];
    $remove_left_padding = $paddings['remove_left_padding'];
    $remove_right_padding = $paddings['remove_right_padding'];
    $extra_class = $misc['extra_class'];

    $this->configFactory->getEditable('layoutcomponents.column')
      ->set('title_text', $title_text)
      ->set('title_color', $title_color)
      ->set('title_type', $title_type)
      ->set('title_align', $title_align)
      ->set('title_size', $title_size)
      ->set('title_border', $title_border)
      ->set('title_border_size', $title_border_size)
      ->set('title_border_color', $title_border_color)
      ->set('background_color', $background_color)
      ->set('border_type', $border_type)
      ->set('border_size', $border_size)
      ->set('border_color', $border_color)
      ->set('border_radius_top_left', $border_radius_top_left)
      ->set('border_radius_top_right', $border_radius_top_right)
      ->set('border_radius_top_right', $border_radius_top_right)
      ->set('border_radius_bottom_left', $border_radius_bottom_left)
      ->set('border_radius_bottom_right', $border_radius_bottom_right)
      ->set('remove_paddings', $remove_paddings)
      ->set('remove_left_padding', $remove_left_padding)
      ->set('remove_right_padding', $remove_right_padding)
      ->set('extra_class', $extra_class)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
