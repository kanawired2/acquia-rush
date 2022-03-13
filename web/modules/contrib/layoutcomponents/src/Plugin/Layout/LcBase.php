<?php

namespace Drupal\layoutcomponents\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layoutcomponents\LcLayoutsManager;
use Drupal\Core\Config\ConfigFactory;
use Drupal\layoutcomponents\Api\Text;
use Drupal\layoutcomponents\Api\Color;
use Drupal\layoutcomponents\Api\Select;
use Drupal\layoutcomponents\Api\Slider;
use Drupal\layoutcomponents\Api\Checkbox;
use Drupal\layoutcomponents\Api\Media;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Layout class for all Layoutcomponents.
 */
class LcBase extends LayoutDefault implements ContainerFactoryPluginInterface {

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $manager;

  /**
   * Config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\Api\Text
   */
  protected $lcApiText;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\Api\Color
   */
  protected $lcApiColor;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\Api\Select
   */
  protected $lcApiSelect;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\Api\Slider
   */
  protected $lcApiSlider;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\Api\Checkbox
   */
  protected $lcApiCheckbox;

  /**
   * Layoutcomponents manager.
   *
   * @var \Drupal\layoutcomponents\Api\Media
   */
  protected $lcApiMedia;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LcLayoutsManager $manager, ConfigFactory $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->manager = $manager;
    $this->configFactory = $config_factory;
    $this->lcApiText = new Text();
    $this->lcApiColor = new Color($this->configFactory);
    $this->lcApiSelect = new Select();
    $this->lcApiSlider = new Slider();
    $this->lcApiCheckbox = new Checkbox();
    $this->lcApiMedia = new Media();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('config.factory')
    );
  }

  /**
   * Provides a default region definition.
   *
   * @return array
   *   Default region array.
   */
  protected function getRegionDefaults() {
    /** @var \Drupal\Core\Config\Config $lc */
    $lc = \Drupal::getContainer()->get('config.factory')->getEditable('layoutcomponents.column');

    return [
      'title' => [
        'title' => $lc->get('title_text'),
      ],
      'styles' => [
        'title' => [
          'type' => $lc->get('title_type'),
          'color' => [
            'settings' => [
              'color' => $lc->get('title_color')['settings']['color'],
              'opacity' => $lc->get('title_color')['settings']['opacity'],
            ],
          ],
          'size' => $lc->get('title_size'),
          'align' => $lc->get('title_align'),
          'border' => $lc->get('title_border'),
          'border_size' => $lc->get('title_border_size'),
          'border_color' => [
            'settings' => [
              'color' => $lc->get('title_border_color')['settings']['color'],
              'opacity' => $lc->get('title_border_color')['settings']['opacity'],
            ],
          ],
        ],
        'border' => [
          'border' => $lc->get('border_type'),
          'size' => $lc->get('border_size'),
          'color' => [
            'settings' => [
              'color' => $lc->get('border_color')['settings']['color'],
              'opacity' => $lc->get('border_color')['settings']['opacity'],
            ],
          ],
          'radius_top_left' => $lc->get('border_radius_top_left'),
          'radius_top_right' => $lc->get('border_radius_top_right'),
          'radius_bottom_left' => $lc->get('border_radius_bottom_left'),
          'radius_bottom_right' => $lc->get('border_radius_bottom_right'),
        ],
        'background' => [
          'color' => [
            'settings' => [
              'color' => $lc->get('background_color')['settings']['color'],
              'opacity' => $lc->get('background_color')['settings']['opacity'],
            ],
          ],
        ],
        'spacing' => [
          'paddings' => $lc->get('remove_paddings'),
          'paddings_left' => $lc->get('remove_left_padding'),
          'paddings_right' => $lc->get('remove_right_padding'),
        ],
        'misc' => [
          'extra_class' => $lc->get('extra_class'),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();

    /** @var \Drupal\Core\Config\Config $lc */
    $lc = \Drupal::getContainer()->get('config.factory')->getEditable('layoutcomponents.section');

    $configuration += [
      'title' => [
        'general' => [
          'title' => $lc->get('title_text'),
          'description' => $lc->get('description_text'),
        ],
        'styles' => [
          'design' => [
            'title_color' => [
              'settings' => [
                'color' => $lc->get('title_color')['settings']['color'],
                'opacity' => $lc->get('title_color')['settings']['opacity'],
              ],
            ],
            'title_type' => $lc->get('title_type'),
            'title_align' => $lc->get('title_align'),
          ],
          'sizing' => [
            'title_size' => $lc->get('title_size'),
          ],
          'border' => [
            'title_border' => $lc->get('title_border'),
            'title_border_size' => $lc->get('title_border_size'),
            'title_border_color' => [
              'settings' => [
                'color' => $lc->get('title_border_color')['settings']['color'],
                'opacity' => $lc->get('title_border_color')['settings']['opacity'],
              ],
            ],
          ],
          'spacing' => [
            'title_margin_top' => $lc->get('title_margin_top'),
            'title_margin_bottom' => $lc->get('title_margin_bottom'),
          ],
          'misc' => [
            'title_extra_class' => '',
            'description_extra_class' => '',
          ],
        ],
      ],
      'section' => [
        'general' => [
          'basic' => [
            'section_type' => $lc->get('section_type'),
            'section_overwrite' => boolval(0),
            'section_label' => '',
            'section_delta' => (int) 0,
          ],
          'structure' => [
            'section_structure_sm' => 12,
            'section_structure' => 12,
            'section_structure_lg' => 12,
            'section_carousel' => boolval(0),
            'section_carousel_slick' => 'none',
          ],
        ],
        'styles' => [
          'background' => [
            'image' => '',
            'background_color' => [
              'settings' => [
                'color' => $lc->get('background_color')['settings']['color'],
                'opacity' => $lc->get('background_color')['settings']['opacity'],
              ],
            ],
          ],
          'sizing' => [
            'full_width' => $lc->get('full_width'),
            'full_width_container' => $lc->get('full_width_container'),
            'full_width_container_title' => $lc->get('full_width_container_title'),
            'height' => $lc->get('height'),
            'height_size' => $lc->get('height_size'),
          ],
          'spacing' => [
            'top_padding' => $lc->get('top_padding'),
            'bottom_padding' => $lc->get('bottom_padding'),
          ],
          'misc' => [
            'extra_class' => $lc->get('extra_class'),
            'extra_attributes' => $lc->get('extra_attributes'),
            'parallax' => (int) 0,
          ],
        ],
      ],
      'regions' => [],
    ];

    // Set config in each region.
    foreach ($this->getPluginDefinition()->getRegions() as $region => $info) {
      $configuration['regions'][$region] = $this->getRegionDefaults();
    }

    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Merge default configuration.
    $this->getConfiguration();

    // Section container.
    $form['container'] = [
      '#type' => 'horizontal_tabs',
      '#title' => $this->t('Settings'),
      '#prefix' => '<div class="lc-lateral-container">',
      '#suffix' => '</div>',
    ];

    // Build Title.
    $form['container']['title'] = [
      '#type' => 'details',
      '#title' => $this->t('Title'),
      '#group' => 'container',
    ];

    $form['container']['title']['container'] = [
      '#type' => 'horizontal_tabs',
      '#title' => $this->t('Title'),
      '#group' => 'title',
    ];

    $form['container']['title']['container'][] = $this->setAdministrativeTitle($form, $form_state);

    // Build Section.
    $form['container']['section'] = [
      '#type' => 'details',
      '#title' => $this->t('Section'),
      '#group' => 'container',
    ];

    $form['container']['section']['container'] = [
      '#type' => 'horizontal_tabs',
      '#title' => $this->t('Section'),
      '#group' => 'section',
    ];

    $form['container']['section']['container'][] = $this->setAdminsitrativeSection($form, $form_state);

    // Build Regions.
    $form['container']['regions'] = [
      '#type' => 'details',
      '#title' => $this->t('Regions'),
      '#group' => 'container',
    ];

    foreach ($this->getPluginDefinition()->getRegionNames() as $region) {
      $form['container']['regions'][$region] = [
        '#type' => 'horizontal_tabs',
        '#title' => $this->t('Regions'),
        '#group' => 'regions',
        '#prefix' => '<div class="lc-lateral-regions">',
        '#suffix' => '</div>',
      ];

      $form['container']['section'][$region][] = $this->setAdminsitrativeRegion($form, $form_state, $region);
    }

    return $form;
  }

  /**
   * Provides list of region components.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   FormState object.
   * @param string $region
   *   Current region.
   *
   * @return array|\Drupal\layout_builder\SectionComponent[]
   *   List of region component.
   */
  private function getComponents(FormStateInterface $form_state, string $region) {
    /** @var \Drupal\Core\Form\FormState $complete_form_state */
    $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;
    /** @var \Drupal\layoutcomponents\Form\LcUpdateColumn $callback_object */
    $callback_object = $complete_form_state->getBuildInfo()['callback_object'];
    $section_storage = $callback_object->getSectionStorage();
    $build_info = $complete_form_state->getBuildInfo();
    $delta = $build_info['args'][1];
    $sections = $section_storage->getSections();
    if (!isset($sections[$delta])) {
      return [];
    }

    $section_data = $sections[$delta];
    return $section_data->getComponentsByRegion($region);
  }

  /**
   * Provide the region configuration.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface object.
   * @param string $region
   *   The region.
   */
  public function setAdminsitrativeRegion(array &$form, FormStateInterface $form_state, $region) {
    $general = NULL;
    $config = $this->getConfiguration()['regions'][$region];
    if (array_key_exists('general', $config)) {
      $general = $config['general'];
    }
    $groups = isset($config['subcolumn']['groups']) ? $config['subcolumn']['groups'] : [];
    $types = isset($config['subcolumn']['types']) ? $config['subcolumn']['types'] : [];
    $classes = isset($config['subcolumn']['classes']) ? $config['subcolumn']['classes'] : [];
    $structures = isset($config['subcolumn']['structures']) ? $config['subcolumn']['structures'] : [];

    $styles = $config['styles'];
    $container = &$form['container']['regions'][$region];

    $container['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'regions',
      'title' => $this->lcApiText->plainText(
        [
          'id' => 'column_' . $region . '-title',
          'title' => $this->t('Title'),
          'description' => $this->t('Set the title of this column'),
          'default_value' => (isset($general['title'])) ? $general['title'] : '',
          'attributes' => [
            'placeholder' => $this->t('Title'),
            'lc' => [
              'type' => 'text',
            ],
          ],
        ]
      ),
    ];

    $column_structures = $this->manager->getColumnOptions(count($this->getPluginDefinition()->getRegionNames()));
    if ($components = $this->getComponents($form_state, $region)) {
      $group_options = [];
      $components = array_values($components);
      $key = 1;
      foreach ($components as $component) {
        $group_options['group_' . $key] = $this->t('Group @group', ['@group' => $key]);
        $key++;
      }

      $container['subcolumn'] = [
        '#type' => 'details',
        '#title' => $this->t('Subcolumns'),
        '#group' => 'regions',
        'groups' => [
          '#type' => 'details',
          '#title' => $this->t('Groups'),
          '#group' => 'regions',
        ],
        'types' => [
          '#type' => 'details',
          '#title' => $this->t('Group Types'),
          '#group' => 'regions',
        ],
        'classes' => [
          '#type' => 'details',
          '#title' => $this->t('Group Classes'),
          '#group' => 'regions',
        ],
        'structures' => [
          '#type' => 'details',
          '#title' => $this->t('Block Classes'),
          '#group' => 'section',
        ],
      ];

      foreach ($components as $component) {
        $cdata = $component->toArray();
        $uuid = $component->getUuid();
        $container['subcolumn']['groups'][$uuid] = $this->lcApiSelect->normal(
          [
            'id' => $uuid,
            'title' => $this->t('Block: @id', ['@id' => $cdata['configuration']['id']]),
            'description' => $this->t('Assign the block to subcolumn group'),
            'options' => $group_options,
            'default_value' => isset($groups[$uuid]) ? $groups[$uuid] : '',
            'attributes' => [
              'lc' => [
                'type' => 'element',
                'group' => 'subcolumn',
              ],
            ],
            'class' => 'type lc_subcolumn-group',
          ]
        );
        $container['subcolumn']['structures'][$uuid] = $this->lcApiText->plainText(
          [
            'id' => 'block_' . md5($uuid),
            'title' => $this->t('Block: @id', ['@id' => $cdata['configuration']['id']]),
            'description' => $this->t('Set the wrapper classes for the column blocks separated by commas'),
            'default_value' => isset($structures[$uuid]) ? $structures[$uuid] : '',
            'attributes' => [
              'placeholder' => $this->t('block_class_1, block_class_2'),
              'lc' => [
                'type' => 'class',
                'style' => 'extra_class',
              ],
            ],
          ]
        );
      }

      $k = 1;
      foreach ($group_options as $key => $group) {
        $container['subcolumn']['types'][$key] = $this->lcApiSelect->normal(
          [
            'id' => 'subcolumn_type_' . $key,
            'title' => $this->t('Group @group', ['@group' => $k]),
            'description' => $this->t('Set the element type for the column group'),
            'options' => [
              'div' => 'Div',
              'span' => 'Span',
              'figure' => 'Figure',
            ],
            'default_value' => isset($types[$key]) ? $types[$key] : '',
            'attributes' => [
              'lc' => [
                'type' => 'element',
              ],
            ],
          ]
        );
        $container['subcolumn']['classes'][$key] = $this->lcApiText->plainText(
          [
            'id' => 'subcolumn_type_' . $key,
            'title' => $this->t('Group @group', ['@group' => $k]),
            'description' => $this->t('Set the classes for the column group'),
            'default_value' => isset($classes[$key]) ? $classes[$key] : '',
            'attributes' => [
              'placeholder' => $this->t('group_class_1, group_class_2'),
              'lc' => [
                'type' => 'class',
                'style' => 'extra_class',
              ],
            ],
          ]
        );
        $k++;
      }

    } // End if components.

    $container['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Styles'),
      '#group' => 'regions',
      'title' => [
        '#type' => 'details',
        '#title' => $this->t('Text'),
        '#group' => 'title',
        'type' => $this->lcApiSelect->normal(
          [
            'id' => 'column_' . $region . '-title',
            'title' => $this->t('Title type'),
            'description' => $this->t('Set the type of title'),
            'default_value' => $styles['title']['type'],
            'options' => $this->manager->getTagOptions(),
            'attributes' => [
              'lc' => [
                'type' => 'element',
              ],
            ],
            'class' => 'type',
          ]
        ),
        'color' => $this->lcApiColor->colorPicker(
          [
            'id' => 'column_' . $region . '-title',
            'title' => $this->t('Title Color'),
            'description' => $this->t('Set the title color'),
            'default_value' =>
              [
                'color' => $styles['title']['color']['settings']['color'],
                'opacity' => $styles['title']['color']['settings']['opacity'],
              ],
            'attributes' => [
              'lc' => [
                'style' => 'color',
                'depend' => [
                  'opacity' => [
                    'color' => 'lc-inline_column_' . $region . '-title-color',
                  ],
                ],
              ],
            ],
            'class' => 'color',
          ]
        ),
        'size' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'column_' . $region . '-title',
            'title' => $this->t('Title size'),
            'description' => $this->t('Set the size of title'),
            'default_value' => $styles['title']['size'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'font-size',
              ],
            ],
            'class' => 'size',
          ]
        ),
        'align' => $this->lcApiSelect->normal(
          [
            'id' => 'column_' . $region . '-title',
            'title' => $this->t('Title align'),
            'description' => $this->t('Set the align of title'),
            'default_value' => $styles['title']['align'],
            'options' => $this->manager->getColumnTitleAlign(),
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'align',
                'class_remove' => 'text-*',
              ],
            ],
            'class' => 'align',
          ]
        ),
        'border' => $this->lcApiSelect->normal(
          [
            'id' => 'column_' . $region . '-container-title',
            'title' => $this->t('Title border'),
            'description' => $this->t('Set the border of title'),
            'default_value' => $styles['title']['border'],
            'options' => $this->manager->getTitleBorder(),
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border',
                'style-value' => 1,
                'lc-after-value' => 'px solid',
                'depend' => [
                  'size' => 'lc-inline_column_' . $region . '-container-title-border-size',
                  'color' => 'lc-inline_column_' . $region . '-container-title-border-color',
                  'opacity' => 'lc-inline_column_' . $region . '-container-title-border-color-opacity',
                ],
              ],
            ],
            'class' => 'border-type',
          ]
        ),
        'border_size' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'column_' . $region . '-container-title',
            'title' => $this->t('Title border size'),
            'description' => $this->t('Set the border size of title'),
            'default_value' => $styles['title']['border_size'],
            'min' => 0,
            'max' => 50,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-size',
                'depend' => [
                  'type' => 'lc-inline_column_' . $region . '-container-title-border-type',
                  'color' => 'lc-inline_column_' . $region . '-container-title-border-color',
                  'opacity' => 'lc-inline_column_' . $region . '-container-title-border-color-opacity',
                ],
              ],
            ],
            'class' => 'border-size',
          ]
        ),
        'border_color' => $this->lcApiColor->colorPicker(
          [
            'id' => 'column_' . $region . '-container-title',
            'title' => $this->t('Title border color'),
            'description' => $this->t('Set the border color of title'),
            'default_value' =>
              [
                'color' => $styles['title']['border_color']['settings']['color'],
                'opacity' => $styles['title']['border_color']['settings']['opacity'],
              ],
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-color',
                'depend' => [
                  'type' => 'lc-inline_column_' . $region . '-container-title-border-type',
                  'size' => 'lc-inline_column_' . $region . '-container-title-border-size',
                  'opacity' => [
                    'color' => 'lc-inline_column_' . $region . '-container-title-border-color',
                    'type' => 'lc-inline_column_' . $region . '-container-title-border-type',
                    'size' => 'lc-inline_column_' . $region . '-container-title-border-size',
                  ],
                ],
              ],
            ],
            'class' => 'border-color',
          ]
        ),
      ],
      'border' => [
        '#type' => 'details',
        '#title' => $this->t('Border'),
        '#group' => 'regions',
        'border' => $this->lcApiSelect->normal(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Type'),
            'description' => $this->t('Set the type of border'),
            'default_value' => $styles['border']['border'],
            'options' => $this->manager->getColumnBorder(),
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border',
                'style-value' => 1,
                'lc-after-value' => 'px solid',
                'depend' => [
                  'size' => "lc-inline_column_$region-border-size",
                  'color' => "lc-inline_column_$region-border-color",
                  'opacity' => "lc-inline_column_$region-border-color-opacity",
                ],
              ],
            ],
            'class' => 'border-type',
          ]
        ),
        'size' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Size'),
            'description' => $this->t('Set the border size of column'),
            'default_value' => $styles['border']['size'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-size',
                'depend' => [
                  'type' => "lc-inline_column_$region-border-type",
                  'color' => "lc-inline_column_$region-border-color",
                  'opacity' => "lc-inline_column_$region-border-color-opacity",
                ],
              ],
            ],
            'class' => 'border-size',
          ]
        ),
        'color' => $this->lcApiColor->colorPicker(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Color'),
            'description' => $this->t('Set the border color of column'),
            'default_value' =>
              [
                'color' => $styles['border']['color']['settings']['color'],
                'opacity' => $styles['border']['color']['settings']['opacity'],
              ],
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-color',
                'depend' => [
                  'type' => "lc-inline_column_$region-border-type",
                  'size' => "lc-inline_column_$region-border-size",
                  'opacity' => [
                    'color' => "lc-inline_column_$region-border-color",
                    'type' => "lc-inline_column_$region-border-type",
                    'size' => "lc-inline_column_$region-border-size",
                  ],
                ],
              ],
            ],
            'class' => 'border-color',
          ]
        ),
        'radius_top_left' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Radius top - left'),
            'description' => $this->t('Set the border radius top - left'),
            'default_value' => $styles['border']['radius_top_left'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-top-left-radius',
              ],
            ],
            'class' => $region . '-border-radius-top-left',
          ]
        ),
        'radius_top_right' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Radius top - right'),
            'description' => $this->t('Set the border radius top - right'),
            'default_value' => $styles['border']['radius_top_right'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-top-right-radius',
              ],
            ],
            'class' => $region . '-border-radius-top-right',
          ]
        ),
        'radius_bottom_left' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Radius bottom - left'),
            'description' => $this->t('Set the border radius bottom - left'),
            'default_value' => $styles['border']['radius_bottom_left'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-bottom-left-radius',
              ],
            ],
            'class' => $region . '-border-radius-bottom_left',
          ]
        ),
        'radius_bottom_right' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Radius bottom - right'),
            'description' => $this->t('Set the border radius bottom - right'),
            'default_value' => $styles['border']['radius_bottom_right'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-bottom-right-radius',
              ],
            ],
            'class' => $region . '-border-radius-bottom_right',
          ]
        ),
      ],
      'background' => [
        '#type' => 'details',
        '#title' => $this->t('Background'),
        '#group' => 'regions',
        'color' => $this->lcApiColor->colorPicker(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Color'),
            'description' => $this->t('Set the background color of column'),
            'default_value' =>
              [
                'color' => $styles['background']['color']['settings']['color'],
                'opacity' => $styles['background']['color']['settings']['opacity'],
              ],
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'background-color',
                'depend' => [
                  'opacity' => [
                    'color' => "lc-inline_column_$region-background-color",
                  ],
                ],
              ],
            ],
            'class' => 'background-color',
          ]
        ),
      ],
      'spacing' => [
        '#type' => 'details',
        '#title' => $this->t('Spacing'),
        '#group' => 'regions',
        'paddings' => $this->lcApiCheckbox->normal(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('No paddings'),
            'description' => $this->t('Remove the spaces betwen columns'),
            'default_value' => $styles['spacing']['paddings'],
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'checkbox',
                'class_checkbox_active' => 'p-0',
                'class_checkbox_disable' => '',
              ],
            ],
            'class' => "$region-paddings",
          ]
        ),
        'paddings_left' => $this->lcApiCheckbox->normal(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('No left padding'),
            'description' => $this->t('Remove left padding'),
            'default_value' => $styles['spacing']['paddings_left'],
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'checkbox',
                'class_checkbox_active' => 'pl-0',
                'class_checkbox_disable' => '',
              ],
            ],
            'class' => "$region-paddings_left",
          ]
        ),
        'paddings_right' => $this->lcApiCheckbox->normal(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('No right padding'),
            'description' => $this->t('Remove right padding'),
            'default_value' => $styles['spacing']['paddings_right'],
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'checkbox',
                'class_checkbox_active' => 'pr-0',
                'class_checkbox_disable' => '',
              ],
            ],
            'class' => "$region-paddings_right",
          ]
        ),
      ],
      'misc' => [
        '#type' => 'details',
        '#title' => $this->t('Misc'),
        '#group' => 'regions',
        'extra_class' => $this->lcApiText->plainText(
          [
            'id' => 'column_' . $region,
            'title' => $this->t('Extra class'),
            'description' => $this->t('Set extra classes in this column, ej. myClass1,myClass2'),
            'default_value' => $styles['misc']['extra_class'],
            'attributes' => [
              'placeholder' => $this->t('Ej. myclass1 myclass2'),
              'lc' => [
                'type' => 'class',
                'style' => 'extra_class',
              ],
            ],
            'class' => '-extra_class',
          ]
        ),
      ],
    ];
  }

  /**
   * Provide the title configuration.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface object.
   */
  public function setAdministrativeTitle(array &$form, FormStateInterface $form_state) {
    $config = $this->getConfiguration()['title'];
    $general = $config['general'];
    $styles = $config['styles'];

    $container = &$form['container']['title']['container'];

    $container['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'title',
      'title' => $this->lcApiText->plainText(
        [
          'id' => 'title',
          'title' => $this->t('Title'),
          'description' => $this->t('Set the title of this section'),
          'default_value' => $general['title'],
          'attributes' => [
            'placeholder' => $this->t('My title'),
            'lc' => [
              'type' => 'text',
            ],
          ],
          'class' => 'title',
        ]
      ),
      'description' => $this->lcApiText->plainTextArea(
        [
          'id' => 'description',
          'title' => $this->t('Description'),
          'description' => $this->t('Set the description of this section'),
          'default_value' => $general['description'],
          'rows' => 10,
          'cols' => 10,
          'attributes' => [
            'placeholder' => $this->t('My Description'),
            'lc' => [
              'type' => 'text',
            ],
          ],
          'class' => 'description',
        ],
      ),
    ];

    $container['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Styles'),
      '#group' => 'title',
      'design' => [
        '#type' => 'details',
        '#title' => $this->t('Text'),
        '#group' => 'title',
        'title_color' => $this->lcApiColor->colorPicker(
          [
            'id' => 'title',
            'title' => $this->t('Color'),
            'description' => $this->t('Set the title color'),
            'default_value' =>
              [
                'color' => $styles['design']['title_color']['settings']['color'],
                'opacity' => $styles['design']['title_color']['settings']['opacity'],
              ],
            'attributes' => [
              'lc' => [
                'style' => 'color',

              ],
            ],
            'class' => 'color',
          ]
        ),
        'title_type' => $this->lcApiSelect->normal(
          [
            'id' => 'title',
            'title' => $this->t('Title type'),
            'description' => $this->t('Set the type of title'),
            'default_value' => $styles['design']['title_type'],
            'options' => $this->manager->getTagOptions(),
            'attributes' => [
              'lc' => [
                'type' => 'element',
              ],
            ],
            'class' => 'title-type',
          ]
        ),
        'title_align' => $this->lcApiSelect->normal(
          [
            'id' => 'title',
            'title' => $this->t('Title align'),
            'description' => $this->t('Set the align of title'),
            'default_value' => $styles['design']['title_align'],
            'options' => $this->manager->getColumnTitleAlign(),
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'align',
                'class_remove' => 'text-*',
              ],
            ],
            'class' => 'title-align',
          ]
        ),
      ],
      'sizing' => [
        '#type' => 'details',
        '#title' => $this->t('Sizing'),
        '#group' => 'title',
        'title_size' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'title',
            'title' => $this->t('Font size'),
            'description' => $this->t('Set the size of title'),
            'default_value' => $styles['sizing']['title_size'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'font-size',
              ],
            ],
            'class' => 'title-size',
          ]
        ),
      ],
      'border' => [
        '#type' => 'details',
        '#title' => $this->t('Border'),
        '#group' => 'title',
        'title_border' => $this->lcApiSelect->normal(
          [
            'id' => 'title',
            'title' => $this->t('Type'),
            'description' => $this->t('Set the border type of title'),
            'default_value' => $styles['border']['title_border'],
            'options' => $this->manager->getTitleBorder(),
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border',
                'style-value' => 1,
                'lc-after-value' => 'px solid',
                'depend' => [
                  'size' => 'lc-inline_title-border-size',
                  'color' => 'lc-inline_title-border-color',
                  'opacity' => 'lc-inline_title-border-color-opacity',
                ],
              ],
            ],
            'class' => 'border-type',
          ]
        ),
        'title_border_size' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'title',
            'title' => $this->t('Size'),
            'description' => $this->t('Set the border size of title'),
            'default_value' => $styles['border']['title_border_size'],
            'min' => 0,
            'max' => 100,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'border-size',
                'depend' => [
                  'type' => 'lc-inline_title-border-type',
                  'color' => 'lc-inline_title-border-color',
                  'opacity' => 'lc-inline_title-border-color-opacity',
                ],
              ],
            ],
            'class' => 'border-size',
          ]
        ),
        'title_border_color' => $this->lcApiColor->colorPicker(
          [
            'id' => 'title',
            'title' => $this->t('Color'),
            'description' => $this->t('Set the border color of title'),
            'default_value' =>
              [
                'color' => $styles['border']['title_border_color']['settings']['color'],
                'opacity' => $styles['border']['title_border_color']['settings']['opacity'],
              ],
            'attributes' => [
              'lc' => [
                'style' => 'border-color',
                'depend' => [
                  'type' => 'lc-inline_title-border-type',
                  'size' => 'lc-inline_title-border-size',
                  'opacity' => [
                    'type' => 'lc-inline_title-border-type',
                    'size' => 'lc-inline_title-border-size',
                  ],
                ],
              ],
            ],
            'class' => 'border-color',
          ]
        ),
      ],
      'spacing' => [
        '#type' => 'details',
        '#title' => $this->t('Spacing'),
        '#group' => 'title',
        'title_margin_top' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'title-container',
            'title' => $this->t('Margin top'),
            'description' => $this->t('Set px of title margin top'),
            'default_value' => $styles['spacing']['title_margin_top'],
            'min' => 0,
            'max' => 500,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'padding-top',
              ],
            ],
            'class' => 'margin-top',
          ]
        ),
        'title_margin_bottom' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'title-container',
            'title' => $this->t('Margin Bottom'),
            'description' => $this->t('Set px of title margin bottom'),
            'default_value' => $styles['spacing']['title_margin_bottom'],
            'min' => 0,
            'max' => 500,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'padding-bottom',
              ],
            ],
            'class' => 'margin-bottom',
          ]
        ),
      ],
      'misc' => [
        '#type' => 'details',
        '#title' => $this->t('Misc'),
        '#group' => 'title',
        'title_extra_class' => $this->lcApiText->plainText(
          [
            'id' => 'description',
            'title' => $this->t('Title - Additional classes'),
            'description' => $this->t('Set extra classes for title, ilegal character will be removed automatically'),
            'default_value' => $styles['misc']['title_extra_class'],
            'attributes' => [
              'placeholder' => $this->t('Ej. myclass1 myclass2'),
              'lc' => [
                'type' => 'class',
                'style' => 'extra_class',
              ],
            ],
            'class' => 'extra_class',
          ]
        ),
        'description_extra_class' => $this->lcApiText->plainText(
          [
            'id' => 'description',
            'title' => $this->t('Description - Additional classes'),
            'description' => $this->t('Set extra classes for description, ilegal character will be removed automatically'),
            'default_value' => $styles['misc']['description_extra_class'],
            'attributes' => [
              'placeholder' => $this->t('Ej. myclass1 myclass2'),
              'lc' => [
                'type' => 'class',
                'style' => 'extra_class',
              ],
            ],
            'class' => 'extra_class',
          ]
        ),
      ],
    ];
  }

  /**
   * Provide the section configuration.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface object.
   */
  public function setAdminsitrativeSection(array &$form, FormStateInterface $form_state) {
    $is_default_storage = FALSE;

    /** @var \Drupal\Core\Form\FormState $complete_form_state */
    $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;

    /** @var \Drupal\layoutcomponents\Form\LcConfigureSection $callback */
    $section_storage = $complete_form_state->getBuildInfo()['callback_object']->getSectionStorage();
    if ($section_storage instanceof DefaultsSectionStorage) {
      $is_default_storage = TRUE;
    }

    $config = $this->getConfiguration()['section'];
    $general = $config['general'];
    $styles = $config['styles'];
    $container = &$form['container']['section']['container'];
    $column_structures = $this->manager->getColumnOptions(count($this->getPluginDefinition()->getRegionNames()));

    $container['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'section',
      'basic' => [
        '#type' => 'details',
        '#title' => $this->t('Basic'),
        '#group' => 'section',
        'section_type' => $this->lcApiSelect->normal(
          [
            'id' => 'section',
            'title' => $this->t('Type'),
            'description' => $this->t('Set the type of this section'),
            'default_value' => $general['basic']['section_type'],
            'options' => $this->manager->getWrapperOptions(),
            'attributes' => [
              'lc' => [
                'type' => 'element',
              ],
            ],
            'class' => 'container-type',
          ]
        ),
        'section_overwrite' => $this->lcApiCheckbox->normal(
          [
            'id' => 'section',
            'title' => $this->t('Disable the overwrite'),
            'description' => $this->t('If you check this chekbox, this section wont be modified from the rest of nodes'),
            'default_value' => $general['basic']['section_overwrite'],
            'class' => 'section-overwrite',
            '#access' => ($is_default_storage) ? TRUE : FALSE,
          ]
        ),
        'section_label' => [
          '#type' => 'textfield',
          '#title' => $this->lcApiText->getLcTitle(
            [
              'title' => $this->t('Label'),
              'description' => $this->t('Set the label'),
            ]
          ),
          '#default_value' => $general['basic']['section_label'] ?: (($is_default_storage) ? \Drupal::currentUser()->id() . \Drupal::time()->getCurrentTime() : ''),
          '#access' => FALSE,
        ],
        'section_delta' => [
          '#type' => 'number',
          '#title' => $this->lcApiSlider->getLcTitle(
            [
              'title' => $this->t('Section delta'),
              'description' => $this->t('Select the delta for the rest of nodes'),
            ]
          ),
          '#default_value' => $general['basic']['section_delta'],
          '#min' => 0,
          '#max' => 1000,
          '#access' => ($is_default_storage) ? TRUE : FALSE,
        ],
      ],
      'structure' => [
        '#type' => 'details',
        '#title' => $this->t('Structure'),
        '#group' => 'section',
        'section_structure_sm' => $this->lcApiSelect->normal(
          [
            'id' => 'row',
            'title' => $this->t('SM Columns Structure'),
            'description' => $this->t('The sizes that appear in this selector are based on the set of combinations that can be created in Bootstrap'),
            'default_value' => $general['structure']['section_structure_sm'],
            'options' => $column_structures,
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'class_remove' => 'col-sm-*',
                'style' => 'column_size',
              ],
            ],
            'class' => 'column-size',
          ]
        ),
        'section_structure' => $this->lcApiSelect->normal(
          [
            'id' => 'row',
            'title' => $this->t('MD Columns Structure'),
            'description' => $this->t('The sizes that appear in this selector are based on the set of combinations that can be created in Bootstrap'),
            'default_value' => $general['structure']['section_structure'],
            'options' => $column_structures,
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'class_remove' => 'col-md-*',
                'style' => 'column_size',
              ],
            ],
            'class' => 'column-size',
          ]
        ),
        'section_structure_lg' => $this->lcApiSelect->normal(
          [
            'id' => 'row',
            'title' => $this->t('LG Columns Structure'),
            'description' => $this->t('The sizes that appear in this selector are based on the set of combinations that can be created in Bootstrap'),
            'default_value' => $general['structure']['section_structure_lg'],
            'options' => $column_structures,
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'class_remove' => 'col-lg-*',
                'style' => 'column_size',
              ],
            ],
            'class' => 'column-size',
          ]
        ),
      ],
    ];

    $container['styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Styles'),
      '#group' => 'section',
      'background' => [
        '#type' => 'details',
        '#title' => $this->t('Background'),
        '#group' => 'section',
        'image' => $this->lcApiMedia->mediaLibrary(
          [
            'id' => 'section',
            'title' => $this->t('Image'),
            'description' => $this->t('Upload a background image'),
            'default_value' => $styles['background']['image'],
            'allowed_bundles' => ['image'],
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'background',
                'depend' => [
                  'color' => 'lc-inline_section-background-color',
                  'opacity' => 'lc-inline_section-background-color-opacity',
                ],
              ],
            ],
            'class' => 'background-image',
          ]
        ),
        'background_color' => $this->lcApiColor->colorPicker(
          [
            'id' => 'section',
            'title' => $this->t('Color'),
            'description' => $this->t('Set the background color of this setion'),
            'default_value' =>
              [
                'color' => $styles['background']['background_color']['settings']['color'],
                'opacity' => $styles['background']['background_color']['settings']['opacity'],
              ],
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'background-color',
                'depend' => [
                  'background' => 'lc-inline_section-background-image',
                  'opacity' => [
                    'background' => 'lc-inline_section-background-image',
                  ],
                ],
              ],
            ],
            'class' => 'background-color',
          ]
        ),
      ],
      'sizing' => [
        '#type' => 'details',
        '#title' => $this->t('Sizing'),
        '#group' => 'section',
        'full_width' => $this->lcApiCheckbox->normal(
          [
            'id' => 'section',
            'title' => $this->t('Full width'),
            'description' => $this->t('Enable full width'),
            'default_value' => $styles['sizing']['full_width'],
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'checkbox',
                'class_checkbox_active' => 'container-fluid',
                'class_checkbox_disable' => 'container',
              ],
            ],
            'class' => 'section-full_width',
          ]
        ),
        'full_width_container' => $this->lcApiCheckbox->normal(
          [
            'id' => 'container-section',
            'title' => $this->t('+ "Container" class'),
            'description' => $this->t('Include the class -Container- in this section'),
            'default_value' => $styles['sizing']['full_width_container'],
            'states' => 'layout_settings[container][section][container][styles][sizing][full_width]',
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'checkbox',
                'class_checkbox_active' => 'container',
                'class_checkbox_disable' => '',
              ],
            ],
            'class' => 'full_width-section-container',
          ]
        ),
        'full_width_container_title' => $this->lcApiCheckbox->normal(
          [
            'id' => 'container-title',
            'title' => $this->t('Title + "Container" class 2'),
            'description' => $this->t('Include the class -Container- in the title'),
            'default_value' => $styles['sizing']['full_width_container_title'],
            'states' => 'layout_settings[container][section][container][styles][sizing][full_width]',
            'attributes' => [
              'lc' => [
                'type' => 'class',
                'style' => 'checkbox',
                'class_checkbox_active' => 'container',
                'class_checkbox_disable' => '',
              ],
            ],
            'class' => 'full_width-title-section',
          ]
        ),
        'height' => $this->lcApiSelect->normal(
          [
            'id' => 'section',
            'title' => $this->t('Height Type'),
            'description' => $this->t('Set the height type'),
            'default_value' => $styles['sizing']['height'],
            'options' =>
              [
                'auto' => $this->t('Auto'),
                'manual' => $this->t('Manual'),
                '100vh' => $this->t('Full'),
                '50vh' => $this->t('Medium'),
              ],
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'height',
                'depend' => [
                  'size' => 'lc-inline_section-height-size',
                ],
              ],
            ],
            'class' => 'height',
          ]
        ),
        'height_size' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'section',
            'title' => $this->t('Height size'),
            'description' => $this->t('Set height of the section'),
            'default_value' => $styles['sizing']['height_size'],
            'min' => 0,
            'max' => 1000,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'height-size',
                'depend' => [
                  'type' => 'lc-inline_section-height',
                ],
              ],
            ],
            'class' => 'height-size',
          ]
        ),
      ],
      'spacing' => [
        '#type' => 'details',
        '#title' => $this->t('Spacing'),
        '#group' => 'section',
        'top_padding' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'section',
            'title' => $this->t('Top padding size'),
            'description' => $this->t('Set the size of top padding'),
            'default_value' => $styles['spacing']['top_padding'],
            'min' => 0,
            'max' => 500,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'padding-top',
                'depend' => [
                  'size' => 'lc-inline_section-top-padding',
                ],
              ],
            ],
            'class' => 'top-padding-size',
          ]
        ),
        'bottom_padding' => $this->lcApiSlider->sliderWidget(
          [
            'id' => 'section',
            'title' => $this->t('Bottom padding size'),
            'description' => $this->t('Set the size of bottom padding'),
            'default_value' => $styles['spacing']['bottom_padding'],
            'min' => 0,
            'max' => 500,
            'attributes' => [
              'lc' => [
                'type' => 'style',
                'style' => 'padding-bottom',
                'depend' => [
                  'size' => 'lc-inline_section-bottom-padding',
                ],
              ],
            ],
            'class' => 'bottom-padding-size',
          ]
        ),
      ],
      'misc' => [
        '#type' => 'details',
        '#title' => $this->t('Misc'),
        '#group' => 'section',
        'extra_class' => $this->lcApiText->plainText(
          [
            'id' => 'section',
            'title' => $this->t('Additional classes'),
            'description' => $this->t('Set extra classes, ilegal character will be removed automatically'),
            'default_value' => $styles['misc']['extra_class'],
            'attributes' => [
              'placeholder' => $this->t('Ej. myclass1 myclass2'),
              'lc' => [
                'type' => 'class',
                'style' => 'extra_class',
              ],
            ],
            'class' => 'extra_class',
          ]
        ),
        'extra_attributes' => $this->lcApiText->plainText(
          [
            'id' => 'section',
            'title' => $this->t('Additional attributes'),
            'description' => $this->t('Set extra attributes, ilegal character will be removed automatically'),
            'default_value' => $styles['misc']['extra_attributes'],
            'attributes' => [
              'placeholder' => $this->t('Ej. id|custom-id role|navigation'),
              'lc' => [
                'type' => 'attribute',
                'style' => 'extra_attributes',
              ],
            ],
            'class' => 'extra_attributes',
          ]
        ),
        'parallax' => $this->lcApiCheckbox->normal(
          [
            'id' => 'section',
            'title' => $this->t('Parallax'),
            'description' => $this->t('Set this section with parallax effect'),
            'default_value' => $styles['misc']['parallax'],
            'attributes' => [
              'lc' => [],
            ],
            'class' => 'section-parallax',
          ]
        ),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('container');

    foreach ($values['regions'] as $name => $region) {
      $data = [];
      if (!isset($region['subcolumn']['groups'])) {
        continue;
      }
      foreach ($region['subcolumn']['groups'] as $uuid => $group) {
        $data[$group][] = $uuid;
      }
      if (!empty($data)) {
        $values['regions'][$name]['subcolumn']['data'] = $data;
      }
    }

    $this->configuration['title'] = $values['title']['container'];
    $this->configuration['section'] = $values['section']['container'];
    $this->configuration['regions'] = $values['regions'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
