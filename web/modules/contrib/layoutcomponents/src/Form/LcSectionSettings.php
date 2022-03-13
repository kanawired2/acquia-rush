<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layoutcomponents\LcLayoutsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Basic fields settings for LayoutComponents.
 */
class LcSectionSettings extends ConfigFormBase {

  /**
   * The Lc manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $layoutManager;

  /**
   * The Lc manager.
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
    return 'layoutcomponents_settings_section';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'layoutcomponents.section',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('layoutcomponents.section');
    $colors = $this->configFactory->getEditable('layoutcomponents.colors')->getRawData()['editor_colors_list'];
    $colors = str_replace(' ', '', $colors);
    $colors = explode(',', $colors);

    $form['general'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Provide the default configuration for the sections'),
      'title' => [
        '#type' => 'details',
        '#title' => $this->t('Title'),
        '#group' => 'general',
        'title_text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Text'),
          '#default_value' => $config->get('title_text') ?: '',
          '#description' => $this->t('Set the default text for the titles of the sections'),
        ],
        'description_text' => [
          '#type' => 'textarea',
          '#title' => $this->t('Description'),
          '#default_value' => $config->get('description_text') ?: '',
          '#description' => $this->t('Set the default description for the sections'),
          '#rows' => 10,
          '#cols' => 10,
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
          '#description' => $this->t('Set the default color for the section titles'),
        ],
        'title_type' => [
          '#type' => 'select',
          '#title' => $this->t('Text type'),
          '#options' => $this->layoutManager->getTagOptions(),
          '#default_value' => $config->get('title_type') ?: 'h1',
          '#description' => $this->t('Set the default type for the section titles'),
        ],
        'title_align' => [
          '#type' => 'select',
          '#title' => $this->t('Text align'),
          '#options' => $this->layoutManager->getColumnTitleAlign(),
          '#default_value' => $config->get('title_align') ?: 'text-left',
          '#description' => $this->t('Set the default align for the section titles'),
        ],
        'title_size' => [
          '#type' => 'number',
          '#title' => $this->t('Text size'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('title_size') ?: (int) 0,
          '#description' => $this->t('Set the default size for the section titles'),
        ],
        'title_border' => [
          '#type' => 'select',
          '#title' => $this->t('Border type'),
          '#options' => $this->layoutManager->getTitleBorder(),
          '#default_value' => $config->get('title_border') ?: 'none',
          '#description' => $this->t('Set the default border type for the section titles'),
        ],
        'title_border_size' => [
          '#type' => 'number',
          '#title' => $this->t('Border size'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('title_border_size') ?: (int) 0,
          '#description' => $this->t('Set the default border size for the section titles'),
        ],
        'title_border_color' => [
          '#type' => 'color_field_element_box',
          '#title' => $this->t('Border color'),
          '#color_options' => $colors,
          '#default_value' => [
            'color' => $config->get('title_border_color')['settings']['color'] ?: '',
            'opacity' => $config->get('title_border_color')['settings']['opacity'] ?: 1,
          ],
          '#description' => $this->t('Set the default border color for the section titles'),
        ],
        'title_margin_top' => [
          '#type' => 'number',
          '#title' => $this->t('Margin top'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('title_margin_top') ?: (int) 0,
          '#description' => $this->t('Set the default margin top for the section titles'),
        ],
        'title_margin_bottom' => [
          '#type' => 'number',
          '#title' => $this->t('Margin bottom'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('title_margin_bottom') ?: (int) 0,
          '#description' => $this->t('Set the default margin bottom for the section titles'),
        ],
      ],
      'section_general' => [
        '#type' => 'details',
        '#title' => $this->t('Section'),
        '#group' => 'general',
        'section_type' => [
          '#type' => 'select',
          '#title' => $this->t('Section type'),
          '#options' => $this->layoutManager->getWrapperOptions(),
          '#default_value' => $config->get('section_type') ?: 'div',
          '#description' => $this->t('Set the default type for the sections'),
        ],
      ],
      'background' => [
        '#type' => 'details',
        '#title' => $this->t('Section - Background'),
        '#group' => 'general',
        'background_color' => [
          '#type' => 'color_field_element_box',
          '#title' => $this->t('Background color'),
          '#color_options' => $colors,
          '#default_value' => [
            'color' => $config->get('background_color')['settings']['color'] ?: '',
            'opacity' => $config->get('background_color')['settings']['opacity'] ?: 1,
          ],
          '#description' => $this->t('Set the default background color for the sections'),
        ],
      ],
      'sizing' => [
        '#type' => 'details',
        '#title' => $this->t('Section - Sizing'),
        '#group' => 'general',
        'full_width' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Full width'),
          '#default_value' => $config->get('full_width') ?: boolval(0),
          '#description' => $this->t('Set the default full width option for the sections'),
        ],
        'full_width_container' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Full width + container'),
          '#default_value' => $config->get('full_width_container') ?: boolval(0),
          '#description' => $this->t('Check this checkbox to use the class container in the sections'),
        ],
        'full_width_container_title' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Full width title + container'),
          '#default_value' => $config->get('full_width_container_title') ?: boolval(0),
          '#description' => $this->t('Check this checkbox to use the class container on title in the sections'),
        ],
        'height' => [
          '#type' => 'select',
          '#title' => $this->t('Height'),
          '#options' => [
            'auto' => $this->t('Auto'),
            'manual' => $this->t('Manual'),
            '100vh' => $this->t('Full'),
            '50vh' => $this->t('Medium'),
          ],
          '#default_value' => $config->get('height') ?: 'auto',
          '#description' => $this->t('Set the default height type for the sections'),
        ],
        'height_size' => [
          '#type' => 'number',
          '#title' => $this->t('Height size'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('height_size') ?: (int) 0,
          '#description' => $this->t('Set the default height size for the sections, this will only apply when the height type is manual'),
        ],
      ],
      'spacing' => [
        '#type' => 'details',
        '#title' => $this->t('Section - Spacing'),
        '#group' => 'general',
        'top_padding' => [
          '#type' => 'number',
          '#title' => $this->t('Top padding'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('top_padding') ?: (int) 0,
          '#description' => $this->t('Set the default top padding size for the sections'),
        ],
        'bottom_padding' => [
          '#type' => 'number',
          '#title' => $this->t('Bottom padding'),
          '#min' => (int) 0,
          '#max' => (int) 500,
          '#default_value' => $config->get('bottom_padding') ?: (int) 0,
          '#description' => $this->t('Set the default bottom padding size for the sections'),
        ],
      ],
      'misc' => [
        '#type' => 'details',
        '#title' => $this->t('Section - Misc'),
        '#group' => 'general',
        'extra_class' => [
          '#type' => 'textfield',
          '#title' => $this->t('Extra class'),
          '#default_value' => $config->get('extra_class') ?: '',
          '#description' => $this->t('Set the default extra class for the sections, ej: myclass1 myclass2'),
        ],
        'extra_attributes' => [
          '#type' => 'textfield',
          '#title' => $this->t('Extra attribute'),
          '#default_value' => $config->get('extra_attributes') ?: '',
          '#description' => $this->t('Set the default extra class for the sections, ej: id|custom-id role|navigation'),
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
    $section = $form_state->getValues()['general']['section_general'];
    $background = $form_state->getValues()['general']['background'];
    $sizing = $form_state->getValues()['general']['sizing'];
    $spacing = $form_state->getValues()['general']['spacing'];
    $misc = $form_state->getValues()['general']['misc'];

    $title_text = $title['title_text'];
    $description_text = $title['description_text'];
    $title_color = $title_styles['title_color'];
    $title_type = $title_styles['title_type'];
    $title_align = $title_styles['title_align'];
    $title_size = $title_styles['title_size'];
    $title_border = $title_styles['title_border'];
    $title_border_color = $title_styles['title_border_color'];
    $title_border_size = $title_styles['title_border_size'];
    $title_margin_top = $title_styles['title_margin_top'];
    $title_margin_bottom = $title_styles['title_margin_bottom'];
    $section_type = $section['section_type'];
    $background_color = $background['background_color'];
    $full_width = $sizing['full_width'];
    $full_width_container = $sizing['full_width_container'];
    $full_width_container_title = $sizing['full_width_container_title'];
    $height = $sizing['height'];
    $height_size = $sizing['height_size'];
    $top_padding = $spacing['top_padding'];
    $bottom_padding = $spacing['bottom_padding'];
    $extra_class = $misc['extra_class'];
    $extra_attributes = $misc['extra_attributes'];

    $this->config('layoutcomponents.section')
      ->set('title_text', $title_text)
      ->set('description_text', $description_text)
      ->set('title_color', $title_color)
      ->set('title_type', $title_type)
      ->set('title_align', $title_align)
      ->set('title_size', $title_size)
      ->set('title_border', $title_border)
      ->set('title_border_size', $title_border_size)
      ->set('title_border_color', $title_border_color)
      ->set('title_margin_top', $title_margin_top)
      ->set('title_margin_bottom', $title_margin_bottom)
      ->set('section_type', $section_type)
      ->set('background_color', $background_color)
      ->set('full_width', $full_width)
      ->set('full_width_container', $full_width_container)
      ->set('full_width_container_title', $full_width_container_title)
      ->set('height', $height)
      ->set('height_size', $height_size)
      ->set('top_padding', $top_padding)
      ->set('bottom_padding', $bottom_padding)
      ->set('extra_class', $extra_class)
      ->set('extra_attributes', $extra_attributes)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
