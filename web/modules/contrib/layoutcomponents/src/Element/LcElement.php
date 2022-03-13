<?php

namespace Drupal\layoutcomponents\Element;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\layout_builder\Element\LayoutBuilder;
use Drupal\Core\Config\ConfigFactory;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;
use Drupal\layoutcomponents\Access\LcAccessHelperTrait;
use Drupal\layoutcomponents\LcDialogHelperTrait;
use Drupal\layoutcomponents\LcSectionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\layoutcomponents\LcLayoutsManager;
use Drupal\layoutcomponents\LcDisplayHelperTrait;
use Drupal\Core\Session\AccountProxy;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * {@inheritdoc}
 */
class LcElement extends LayoutBuilder {

  use LcDisplayHelperTrait;
  use LcDialogHelperTrait;
  use LcAccessHelperTrait;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The Layout Tempstore.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstore;

  /**
   * The LC manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $lcLayoutManager;

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Drupal\layoutcomponents\LcSectionManager definition.
   *
   * @var \Drupal\layoutcomponents\LcSectionManager
   */
  protected $lcSectionManager;

  /**
   * Current Entity.
   *
   * @var \stdClass
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LayoutTempstoreRepositoryInterface $layout_tempstore_repository, MessengerInterface $messenger, ThemeHandlerInterface $theme_handler, ConfigFactory $config_factory, PrivateTempStoreFactory $temp_store, LcLayoutsManager $layout_manager, AccountProxy $current_user, LcSectionManager $lc_section_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $layout_tempstore_repository, $messenger);
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;
    $this->tempStoreFactory = $temp_store;
    $this->layoutTempstore = $layout_tempstore_repository;
    $this->lcLayoutManager = $layout_manager;
    $this->currentUser = $current_user;
    $this->lcSectionManager = $lc_section_manager;
    $this->entity = $this->getCurrentEntity();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('layout_builder.tempstore_repository'),
      $container->get('messenger'),
      $container->get('theme_handler'),
      $container->get('config.factory'),
      $container->get('tempstore.private'),
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('current_user'),
      $container->get('layoutcomponents.section')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function layout(SectionStorageInterface $section_storage) {
    $output = parent::layout($section_storage);
    $output['#attached']['library'][] = 'layoutcomponents/layoutcomponents.editform';

    // Add bootstrap barrio theme.
    $themes = $this->themeHandler->listInfo();
    if (!array_key_exists('bootstrap4', $themes) && !array_key_exists('bootstrap_barrio', $themes) ) {
      $this->messenger->addError($this->t('To use LayoutComponents is completely necessary Bootstrap4 theme.'));
      $output = [];
    }

    // Storage settings.
    $storage_type = $section_storage->getStorageType();
    $storage_id = $section_storage->getStorageId();

    // Allow remove clipboard.
    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = $this->tempStoreFactory->get('lc');
    $data = $store->get('lc_element');
    $clipboard_attr = (!empty($data)) ?: 'hidden';

    // Remove the content of clipboard.
    $output['clipboard'] = [
      '#type' => 'link',
      '#title' => 'Remove clipboard',
      '#url' => Url::fromRoute('layoutcomponents.copy_remove',
      [
        'section_storage_type' => $storage_type,
        'section_storage' => $storage_id,
      ],
      [
        'attributes' => [
          'class' => [
            'use-ajax',
            'lc_editor-link',
            'layout-builder__link',
            'lc-clipboard',
            $clipboard_attr,
          ],
          'data-dialog-type' => 'dialog',
          'data-dialog-renderer' => 'off_canvas',
          'data-dialog-options' => $this->dialogOptions(),
          'title' => $this->t('Remove the content of clipboard'),
        ],
      ]),
      '#weight' => -1,
    ];

    // Hide sub sections.
    foreach ($output as $delta => $section) {
      if (!is_numeric($delta)) {
        continue;
      }

      if (!array_key_exists('layout-builder__section', $section)) {
        continue;
      }

      if (array_key_exists('sub_section', $section['layout-builder__section']['#settings'])) {
        unset($output[$delta]);
      }
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareLayout(SectionStorageInterface $section_storage) {
    if (!$section_storage instanceof DefaultsSectionStorage) {
      // Content sections.
      $sections = $this->getOrderedSections($section_storage);

      // Set the rest of defaults.
      foreach ($sections as $delta => $section) {
        if (!isset($section)) {
          continue;
        }
        if ($section->getLayoutId() == 'layout_builder_blank') {
          continue;
        }
        $section_storage->appendSection($section);
      }

      // TODO: Is neccesary register the changes in the temp store repository instead of save object,
      // TODO: if not the option "Discard changes" won't works correctly.
      $this->layoutTempstore->set($section_storage);
    }

    // Send the new structure to the default event.
    parent::prepareLayout($section_storage);
  }

  /**
   * {@inheritdoc}
   */
  public function buildAddSectionLink(SectionStorageInterface $section_storage, $delta, $sub_section = []) {
    $build = parent::buildAddSectionLink($section_storage, $delta);
    $build['link']['#title'] = '';

    // Alter Add Section button.
    /** @var \Drupal\Core\Url $url */
    $url = $build['link']['#url'];

    // Set update_layout if is "Add section".
    $url->setRouteParameter('update_layout', 0);

    // Set this section as sub section.
    $url->setRouteParameter('sub_section', $sub_section);


    // Remove link--add class.
    $options = $url->getOptions();
    $options['attributes']['class'] = [
      'use-ajax',
      'link-rounded',
      'lc_editor-link',
      'layout-builder__link',
      'layout-builder__link-add-section',
    ];
    $options['attributes']['data-dialog-options'] = $this->dialogOptions();
    $options['attributes']['title'] = $this->t('Add new section');

    // Check if a section is ready to copy.
    $store = $this->tempStoreFactory->get('lc');
    $data = $store->get('lc_element');
    $options['attributes']['class']['lc-copy'] = ((isset($data)) && ($data['type'] == 'section')) ? 'lc-copy' : '';

    // Save new options.
    $url->setOptions($options);

    if (isset($this->entity) && !$this->getAccess($this->currentUser, 'create ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' sections')) {
      unset($build['link']['#url']);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildAdministrativeSection(SectionStorageInterface $section_storage, $delta, $region = 'first') {
    $build = parent::buildAdministrativeSection($section_storage, $delta);

    // Storage settings.
    $storage_type = $section_storage->getStorageType();
    $storage_id = $section_storage->getStorageId();
    $section = $section_storage->getSection($delta);
    $layout = $section->getLayout();
    $layout_definition = $layout->getPluginDefinition();
    $is_sub_section = $this->lcSectionManager->isSubSection($section_storage, $delta);


    // Alter configure button.
    $configure['configure'] = $build['configure'];
    $configure['configure']['#url'] = $this->addTooltip($configure['configure']['#url'], 'Configure this section');
    $configure['configure']['#title'] = '';
    $configure['configure']['#attributes']['data-dialog-options'] = $this->dialogOptions();
    $configure['configure']['#attributes']['class'] = [
      'use-ajax',
      'lc_editor-link',
      'layout-builder__section_link',
      'layout-builder__section_link-configure',
    ];

    // Alter remove button.
    $remove['remove'] = $build['remove'];
    $remove['remove']['#url'] = $this->addTooltip($remove['remove']['#url'], 'Remove this section');
    $remove['remove']['#title'] = '';
    $remove['remove']['#attributes']['data-dialog-options'] = $this->dialogOptions();
    $remove['remove']['#attributes']['class'] = [
      'use-ajax',
      'lc_editor-link',
      'layout-builder__section_link',
      'layout-builder__section_link-remove',
    ];

    // Add change layout button.
    $update = [
      'move_layout' => [
        '#type' => 'link',
        '#title' => '',
        '#url' => Url::fromRoute('layout_builder.move_sections_form',
          [
            'section_storage_type' => $storage_type,
            'section_storage' => $storage_id,
            'sub_section' => ($is_sub_section) ? ['delta' => $delta] : '',
          ],
          [
            'attributes' => [
              'class' => [
                'use-ajax',
                'lc_editor-link',
                'layout-builder__section_link',
                'layout-builder__section_link-move',
              ],
              'data-dialog-type' => 'dialog',
              'data-dialog-renderer' => 'off_canvas',
              'data-dialog-options' => $this->dialogOptions(),
              'title' => $this->t('Move section'),
            ],
          ]
        ),
        '#weight' => -1,
      ],
      'update_layout' => [
        '#type' => 'link',
        '#title' => '',
        '#url' => Url::fromRoute('layout_builder.choose_section',
          [
            'section_storage_type' => $storage_type,
            'section_storage' => $storage_id,
            'delta' => $delta,
            'update_layout' => 1,
          ],
          [
            'attributes' => [
              'class' => [
                'use-ajax',
                'lc_editor-link',
                'layout-builder__section_link',
                'layout-builder__section_link-update',
              ],
              'data-dialog-type' => 'dialog',
              'data-dialog-renderer' => 'off_canvas',
              'data-dialog-options' => $this->dialogOptions(),
              'title' => $this->t('Change layout'),
            ],
          ]
        ),
      ],
      /*'copy' => [
        '#type' => 'link',
        '#title' => '',
        '#url' => Url::fromRoute('layoutcomponents.copy_section',
          [
            'section_storage_type' => $storage_type,
            'section_storage' => $storage_id,
            'delta' => $delta,
          ],
          [
            'attributes' => [
              'class' => [
                'use-ajax',
                'lc_editor-link',
                'layout-builder__section_link',
                'layout-builder__section_link-copy',
              ],
              'data-dialog-type' => 'dialog',
              'data-dialog-renderer' => 'off_canvas',
              'data-dialog-options' => $this->dialogOptions(),
              'title' => $this->t('Copy to clipboard'),
            ],
          ]
        ),
      ],*/
    ];

    // Section access control.
    if (isset($this->entity)) {
      if (!$this->getAccess($this->currentUser, 'configure all ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' sections')) {
        unset($configure['configure']);
      }

      if (!$this->getAccess($this->currentUser, 'remove all ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' sections')) {
        unset($remove['remove']);
      }

      if (!$this->getAccess($this->currentUser, 'move all ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' sections')) {
        unset($update['move_layout']);
      }

      if (!$this->getAccess($this->currentUser, 'change all ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' layout sections')) {
        unset($update['update_layout']);
      }

      if (!$this->getAccess($this->currentUser, 'copy all ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' sections')) {
        unset($update['copy']);
      }
    }

    // Reorder section links.
    $new_config = $remove + $configure + $update;
    $new_config['#type'] = 'container';
    $new_config['#attributes']['class'] = 'layout_builder__configure_section_items';
    $new_config['#weight'] = -1;

    // Remove old buttons.
    unset($build['configure']);
    unset($build['remove']);

    $build['layout-builder__configure_section'] = $new_config;

    foreach ($layout_definition->getRegions() as $region => $info) {

      // Blocks.
      $section = &$build['layout-builder__section'];
      if (!empty($section[$region])) {
        foreach (Element::children($section[$region]) as $uuid) {
          if (array_key_exists('#contextual_links', $section[$region][$uuid])) {
            // Implement buildAdministrativeBlock().
            $section[$region][$uuid]['content']['layout_builder-configuration'] = $this->buildAdministrativeBlock($storage_type, $storage_id, $delta, $region, $uuid);
          }
        }
      }

      // Process Add Block button.
      $addBlock = &$build['layout-builder__section'][$region]['layout_builder_add_block'];
      $addBlock['link']['#title'] = '';
      $addBlock['#weight'] = 999;

      /** @var \Drupal\Core\Url $url */
      $url = $addBlock['link']['#url'];

      // Remove link--add class.
      $options = $url->getOptions();
      $options['attributes']['class'] = [
        'use-ajax',
        'lc_editor-link',
        'link-rounded',
        'layout-builder__column_link',
        'layout-builder__column_link-add',
      ];
      $options['attributes']['data-dialog-options'] = $this->dialogOptions();
      $options['attributes']['title'] = $this->t('Add new block');

      // Check if a block is ready to copy.
      $store = $this->tempStoreFactory->get('lc');
      $data = $store->get('lc_element');
      $options['attributes']['class'][] = ((isset($data)) && ($data['type'] == 'block')) ? 'lc-copy' : '';

      $url->setOptions($options);

      // Include new Add section in columns.
      $build['layout-builder__section'][$region]['layout_builder_add_section']['link'] = $this->buildAddSectionLink($section_storage, $delta, [
        'parent_section' => $delta,
        'parent_region' => $region,
      ]);
      $build['layout-builder__section'][$region]['layout_builder_add_section']['#weight'] = 1000;

      // Column.
      $configureSection = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['layout-builder__configure-column'],
        ],
        '#weight' => 1,
      ];

      // Check if a column is ready to copy.
      $store = $this->tempStoreFactory->get('lc');
      $data = $store->get('lc_element');
      $copy = ((isset($data)) && ($data['type'] == 'column')) ? 'lc-copy' : '';

      $configureSection['configure'] = [
        '#type' => 'link',
        '#title' => '',
        '#url' => Url::fromRoute('layoutcomponents.update_column',
          [
            'section_storage_type' => $storage_type,
            'section_storage' => $storage_id,
            'delta' => $delta,
            'region' => $region,
          ],
          [
            'attributes' => [
              'class' => [
                'use-ajax',
                'lc_editor-link',
                'layout-builder__column_link',
                'layout-builder__column_link-configure',
                $copy,
              ],
              'data-dialog-type' => 'dialog',
              'data-dialog-renderer' => 'off_canvas',
              'data-dialog-options' => $this->dialogOptions(),
              'title' => $this->t('Configure column'),
            ],
          ]
        ),
      ];

      if ($copy !== 'lc-copy') {
        /*$configureSection['copy'] = [
          '#type' => 'link',
          '#title' => '',
          '#url' => Url::fromRoute('layoutcomponents.copy_column',
            [
              'section_storage_type' => $storage_type,
              'section_storage' => $storage_id,
              'delta' => $delta,
              'region' => $region,
            ],
            [
              'attributes' => [
                'class' => [
                  'use-ajax',
                  'lc_editor-link',
                  'layout-builder__column_link',
                  'layout-builder__column_link-copy',
                ],
                'data-dialog-type' => 'dialog',
                'data-dialog-renderer' => 'off_canvas',
                'data-dialog-options' => $this->dialogOptions(),
                'title' => $this->t('Copy to clipboard'),
              ],
            ]
          ),
        ];*/
      }

      // Column access control.
      if (isset($this->entity)) {
        if (!$this->getAccess($this->currentUser, 'add ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' blocks')) {
          unset($build['layout-builder__section'][$region]['layout_builder_add_block']);
        }

        if (!$this->getAccess($this->currentUser, 'configure all ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' columns')) {
          unset($configureSection['configure']);
        }

        if (!$this->getAccess($this->currentUser, 'copy all ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' columns')) {
          unset($configureSection['copy']);
        }
      }

      // Reorder block links.
      $build['layout-builder__section'][$region][] = $configureSection;

      // Include sub sections.
      $current_layout_settings = $section_storage->getSection($delta)->getLayoutSettings();

      /** @var \Drupal\layout_builder\Section $dd_section */
      foreach ($section_storage->getSections() as $dd => $dd_section) {
        $dd_settings = $dd_section->getLayoutSettings();
        if (empty($dd_settings['sub_section'])) {
          continue;
        }

        if (!array_key_exists('lc_id', $dd_settings['sub_section'])) {
          continue;
        }

        if (!array_key_exists('lc_id', $current_layout_settings)) {
          $current_layout_settings['lc_id'] = \Drupal::service('uuid')->generate();
          $section_storage->getSection($delta)->setLayoutSettings($current_layout_settings);
        }

        if ($dd_settings['sub_section']['lc_id'] == $current_layout_settings['lc_id'] && $dd_settings['sub_section']['parent_region'] == $region) {
          $build['layout-builder__section'][$region]['sub_section'][] = $this->buildAdministrativeSection($section_storage, $dd, $region);
        }
      }
    }

    return $build;
  }

  /**
   * Builds the render array for the layout block while editing.
   *
   * @param string $storage_type
   *   The section storage.
   * @param string $storage_id
   *   The storage id.
   * @param int $delta
   *   The delta of the section.
   * @param string $region
   *   The region.
   * @param string $uuid
   *   The uuid.
   *
   * @return array
   *   The render array for a given block.
   */
  public function buildAdministrativeBlock($storage_type, $storage_id, $delta, $region, $uuid) {
    $configureBlock = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['layout-builder__configure-block'],
      ],
      '#weight' => -2,
    ];

    $configureBlock['move'] = [
      '#type' => 'link',
      '#title' => '',
      '#url' => Url::fromUserInput('#',
        [
          'attributes' => [
            'class' => [
              'use-ajax',
              'lc_editor-link',
              'layout-builder__block_link',
              'layout-builder__block_link-move',
            ],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
            'data-dialog-options' => $this->dialogOptions(),
            'title' => $this->t('Move block'),
          ],
        ]
      ),
    ];

    $configureBlock['remove'] = [
      '#type' => 'link',
      '#title' => '',
      '#url' => Url::fromRoute('layout_builder.remove_block',
        [
          'section_storage_type' => $storage_type,
          'section_storage' => $storage_id,
          'delta' => $delta,
          'region' => $region,
          'uuid' => $uuid,
        ],
        [
          'attributes' => [
            'class' => [
              'use-ajax',
              'lc_editor-link',
              'layout-builder__block_link',
              'layout-builder__block_link-remove',
            ],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
            'data-dialog-options' => $this->dialogOptions(),
            'title' => $this->t('Remove block'),
          ],
        ]
      ),
    ];

    $configureBlock['configure'] = [
      '#type' => 'link',
      '#title' => '',
      '#url' => Url::fromRoute('layout_builder.update_block',
        [
          'section_storage_type' => $storage_type,
          'section_storage' => $storage_id,
          'delta' => $delta,
          'region' => $region,
          'uuid' => $uuid,
        ],
        [
          'attributes' => [
            'class' => [
              'use-ajax',
              'lc_editor-link',
              'layout-builder__block_link',
              'layout-builder__block_link-configure',
            ],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
            'data-dialog-options' => $this->dialogOptions(),
            'title' => $this->t('Configure block'),
            'resizable' => TRUE,
          ],
        ]
      ),
    ];

    /*$configureBlock['copy'] = [
      '#type' => 'link',
      '#title' => '',
      '#url' => Url::fromRoute('layoutcomponents.copy_block',
        [
          'section_storage_type' => $storage_type,
          'section_storage' => $storage_id,
          'delta' => $delta,
          'region' => $region,
          'uuid' => $uuid,
        ],
        [
          'attributes' => [
            'class' => [
              'use-ajax',
              'lc_editor-link',
              'layout-builder__block_link',
              'layout-builder__block_link-copy',
            ],
            'data-dialog-type' => 'dialog',
            'data-dialog-renderer' => 'off_canvas',
            'data-dialog-options' => $this->dialogOptions(),
            'title' => $this->t('Copy to clipboard'),
            'resizable' => TRUE,
          ],
        ]
      ),
    ];*/

    // Block access control.
    if (isset($this->entity)) {
      if (!$this->getAccess($this->currentUser, 'move ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' blocks')) {
        unset($configureBlock['move']);
      }

      if (!$this->getAccess($this->currentUser, 'remove ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' blocks')) {
        unset($configureBlock['remove']);
      }

      if (!$this->getAccess($this->currentUser, 'configure ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' blocks')) {
        unset($configureBlock['configure']);
      }

      if (!$this->getAccess($this->currentUser, 'copy ' . $this->entity->bundle() . ' ' . $this->entity->getEntityTypeId() . ' blocks')) {
        unset($configureBlock['copy']);
      }
    }

    return $configureBlock;
  }

  /**
   * Provide tooltip for Url elements.
   *
   * @param \Drupal\Core\Url $url
   *   The section storage.
   * @param string $text
   *   The text.
   *
   * @return \Drupal\Core\Url
   *   The url preprocessed.
   */
  public function addTooltip(Url $url, $text) {
    $options = $url->getOptions();
    $options['attributes']['title'] = $text;
    $url->setOptions($options);

    return $url;
  }

}
