<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\Layout\LayoutPluginManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Component\Uuid\UuidInterface;

/**
 * Class LCLayoutsManager.
 */
class LcLayoutsManager {

  use StringTranslationTrait;

  /**
   * Section options.
   *
   * @var array
   */
  protected $wrapperOptions = [];

  /**
   * Templates.
   *
   * @var array
   */
  protected $templates = [];

  /**
   * Section size.
   *
   * @var array
   */
  protected $wrapperSize = [];

  /**
   * Section top space.
   *
   * @var array
   */
  protected $wrapperTopSpace = [];

  /**
   * Section content align.
   *
   * @var array
   */
  protected $wrapperContentAlign = [];

  /**
   * Column name.
   *
   * @var array
   */
  protected $columnName = [];

  /**
   * Column title align.
   *
   * @var array
   */
  protected $columnTitleAlign = [];

  /**
   * Column border.
   *
   * @var array
   */
  protected $columnBorder = [];

  /**
   * List of ayouts.
   *
   * @var array
   */
  protected $layouts = [];

  /**
   * Layout Plugin Manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * Constructs a new \Drupal\layoutcomponents\LcLayoutsManager object.
   */
  public function __construct(LayoutPluginManager $layout_plugin_manager, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository, AccountInterface $current_user, UuidInterface $uuid) {
    $this->wrapperOptions = [
      'div' => 'Div',
      'span' => 'Span',
      'section' => 'Section',
      'article' => 'Article',
      'header' => 'Header',
      'footer' => 'Footer',
      'aside' => 'Aside',
      'figure' => 'Figure',
    ];

    $this->tagOptions = [
      'div' => 'Div',
      'span' => 'Span',
      'h1' => 'H1',
      'h2' => 'H2',
      'h3' => 'H3',
      'h4' => 'H4',
      'h5' => 'H5',
      'h6' => 'H6',
    ];

    $this->templates = [
      'layoutcomponents-one-column' => 1,
      'layoutcomponents-two-column' => 2,
      'layoutcomponents-three-column' => 3,
      'layoutcomponents-four-column' => 4,
      'layoutcomponents-five-column' => 5,
      'layoutcomponents-six-column' => 6,
    ];

    $this->wrapperSize = [
      'col-md-1' => '1 column',
      'col-md-2' => '2 columns',
      'col-md-3' => '3 columns',
      'col-md-4' => '4 columns',
      'col-md-5' => '5 columns',
      'col-md-6' => '6 columns',
      'col-md-7' => '7 columns',
      'col-md-8' => '8 columns',
      'col-md-9' => '9 columns',
      'col-md-10' => '10 columns',
      'col-md-11' => '11 columns',
      'col-md-12' => '12 columns',
    ];

    $this->wrapperTopSpace = [
      'simple-margin-none' => 'None',
      'simple-margin-small' => 'Small',
      'simple-margin-medium' => 'Medium',
      'simple-margin-large' => 'Large',
    ];

    $this->wrapperContentAlign = [
      'justify-content-start' => 'Left',
      'justify-content-center' => 'Center',
      'justify-content-end' => 'Right',
    ];

    $this->columnName = [
      0 => 'first',
      1 => 'second',
      2 => 'third',
      3 => 'quarter',
      4 => 'fifth',
      5 => 'sixth',
    ];

    $this->columnTitleAlign = [
      'text-left' => 'Left',
      'text-center' => 'Center',
      'text-right' => 'Right',
    ];

    $this->titleBorder = [
      'none' => 'None',
      'left' => 'Left',
      'top' => 'Top',
      'right' => 'Right',
      'bottom' => 'Bottom',
      'all' => 'All',
    ];

    $this->columnBorder = [
      'none' => 'None',
      'left' => 'Left',
      'top' => 'Top',
      'right' => 'Right',
      'bottom' => 'Bottom',
      'all' => 'All',
    ];

    $this->layoutPluginManager = $layout_plugin_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->currentUser = $current_user;
    $this->uuidGenerator = $uuid;
    $this->getLayoutComponentsLayouts();
  }

  /**
   * Set the layouts filtered by LC class.
   */
  protected function getLayoutComponentsLayouts() {
    $layoutList = $this->layoutPluginManager->getDefinitions();
    foreach ($layoutList as $name => $layout) {
      /** @var \Drupal\Core\Layout\LayoutDefinition $layout */
      if ($layout->getClass() === 'Drupal\layoutcomponents\Plugin\Layout\LcBase') {
        $this->layouts[] = $layout;
      }
    }
  }

  /**
   * Get the wrapper options.
   *
   * @return array
   *   The wrapper options.
   */
  public function getWrapperOptions() {
    return $this->wrapperOptions;
  }

  /**
   * Get the tag options.
   *
   * @return array
   *   The tag options.
   */
  public function getTagOptions() {
    return $this->tagOptions;
  }

  /**
   * Get the wrapper size.
   *
   * @return array
   *   The wrapper size.
   */
  public function getWrapperSize() {
    return $this->wrapperSize;
  }

  /**
   * Get the wrapper top space.
   *
   * @return array
   *   The wrapper top space.
   */
  public function getWrappetTopSpace() {
    return $this->wrapperTopSpace;
  }

  /**
   * Get the wrapper content align.
   *
   * @return array
   *   The wrapper content align.
   */
  public function getWrappetContentAlign() {
    return $this->wrapperContentAlign;
  }

  /**
   * Get the wrapper title align.
   *
   * @return array
   *   The wrapper title align.
   */
  public function getColumnTitleAlign() {
    return $this->columnTitleAlign;
  }

  /**
   * Get the title border.
   *
   * @return array
   *   The title border
   */
  public function getTitleBorder() {
    return $this->titleBorder;
  }

  /**
   * Get the column border.
   *
   * @return array
   *   The wrapper column border.
   */
  public function getColumnBorder() {
    return $this->columnBorder;
  }

  /**
   * Get the column name.
   *
   * @return array
   *   The column name.
   */
  public function getColumName($index) {
    return $this->columnName[$index];
  }

  /**
   * Get the column options.
   *
   * @return array
   *   The column options.
   */
  public function getColumnOptions($type) {
    if (isset($type)) {
      $options = ["12" => "100"];
      switch ($type) {
        case 1:
          $options = [
            "1" => '1',
            "2" => '2',
            "3" => '3',
            "4" => '4',
            "5" => '5',
            "6" => '6',
            "7" => '7',
            "8" => '8',
            "9" => '9',
            "10" => '10',
            "11" => '11',
            "12" => '12',
          ];
          break;

        case 2:
          $options = [
            "12/12" => '12/12',
            "0/12" => '0/12',
            "1/11" => '1/11',
            "2/10" => '2/10',
            "3/9" => '3/9',
            "4/8" => '4/8',
            "5/7" => '5/7',
            "6/6" => '6/6',
            "7/5" => '7/5',
            "8/4" => '8/4',
            "9/3" => '9/3',
            "10/2" => '10/2',
            "11/1" => '11/1',
            "12/0" => '12/0',
          ];
          break;

        case 3:
          $options = [
            "12/12/12" => '12/12/12',
            "12/0/0" => '12/0/0',
            "11/1/0" => '11/1/0',
            "10/2/0" => '10/2/0',
            "9/3/0" => '9/3/0',
            "8/4/0" => '8/4/0',
            "7/5/0" => '7/5/0',
            "6/6/0" => '6/6/0',
            "5/7/0" => '5/7/0',
            "4/8/0" => '4/8/0',
            "3/9/0" => '3/9/0',
            "2/10/0" => '2/10/0',
            "1/11/0" => '1/11/0',
            "0/12/0" => '0/12/0',
            "11/0/1" => '11/0/1',
            "10/0/2" => '10/0/2',
            "9/0/3" => '9/0/3',
            "8/0/4" => '8/0/4',
            "7/0/5" => '7/0/5',
            "6/0/6" => '6/0/6',
            "5/0/7" => '5/0/7',
            "4/0/8" => '4/0/8',
            "3/0/9" => '3/0/9',
            "2/0/10" => '2/0/10',
            "1/0/11" => '1/0/11',
            "0/0/12" => '0/0/12',
            "3/6/3" => '3/6/3',
            "4/4/4" => '4/4/4',
            "3/3/6" => '3/3/6',
            "6/3/3" => '6/3/3',
          ];
          break;

        case 4:
          $options = [
            "12/12/12/12" => '12/12/12/12',
            "12/0/0/0" => '12/0/0/0',
            "0/12/0/0" => '0/12/0/0',
            "0/0/12/0" => '0/0/12/0',
            "0/0/0/12" => '0/0/0/12',
            "3/3/3/3" => '3/3/3/3',
            "6/2/2/2" => '6/2/2/2',
            "2/6/2/2" => '2/6/2/2',
            "2/2/6/2" => '2/2/6/2',
            "2/2/2/6" => '2/2/2/6',
          ];
          break;

        case 5:
          $options = [
            "12/12/12/12/12" => '12/12/12/12/12',
            "12/0/0/0/0" => '12/0/0/0/0',
            "0/12/0/0/0" => '0/12/0/0/0',
            "0/0/12/0/0" => '0/0/12/0/0',
            "0/0/0/12/0" => '0/0/0/12/0',
            "0/0/0/0/12" => '0/0/0/0/12',
            "3/2/2/2/3" => '3/2/2/2/3',
          ];
          break;

        case 6:
          $options = [
            "12/12/12/12/12/12" => '12/12/12/12/12/12',
            "12/0/0/0/0/0" => '12/0/0/0/0/0',
            "0/12/0/0/0/0" => '0/12/0/0/0/0',
            "0/0/12/0/0/0" => '0/0/12/0/0/0',
            "0/0/0/12/0/0" => '0/0/0/12/0/0',
            "0/0/0/0/12/0" => '0/0/0/0/12/0',
            "0/0/0/0/0/12" => '0/0/0/0/0/12',
            "2/2/2/2/2/2" => '2/2/2/2/2/2',
          ];
          break;
      }
    }
    return $options;
  }

  /**
   * Get the number of columns.
   *
   * @return array
   *   The number of column.
   */
  public function getNumberOfColumns($template) {
    return $this->templates[$template];
  }

  /**
   * Convert hex color to rgba.
   *
   * @param string $hex
   *   The color as hex.
   * @param string $opacity
   *   The opacity.
   *
   * @return string
   *   The color converted to rgb|rgba.
   */
  public function hexToRgba($hex, $opacity = NULL) {
    if (isset($hex) && isset($opacity)) {
      [$r, $g, $b] = sscanf($hex, "#%02x%02x%02x");
      $background_color = 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $opacity . ')';
    }
    else {
      [$r, $g, $b] = sscanf($hex, "#%02x%02x%02x");
      $background_color = 'rgb(' . $r . ',' . $g . ',' . $b . ')';
    }
    return $background_color;
  }

  /**
   * Clone the block.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage being configured.
   * @param int $delta
   *   The delta.
   * @param string $region
   *   The region.
   * @param \Drupal\layout_builder\SectionComponent $component
   *   The component.
   */
  public function duplicateBlock(SectionStorageInterface &$section_storage, $delta, $region, SectionComponent $component) {

    $new_uuid = '';
    while (TRUE) {
      $new_uuid = $this->uuidGenerator->generate();
      if ($this->checkUuid($section_storage, $new_uuid)) {
        break;
      }
    }

    $inline = $component->getPlugin();
    $block = $inline->build()['#block_content'];

    // Generate the duplicate of parent block.
    $block = $block->createDuplicate();
    $block->setNonReusable();
    $block->enforceIsNew();
    $block->save();
    $component = $component->toArray();

    // The field definitions.
    $fields = $block->getFieldDefinitions();

    /** @var \Drupal\field\Entity\FieldConfig $field */
    foreach ($fields as $name => $field) {
      // Filter by config.
      if ($field instanceof FieldConfig) {
        // IF the block have references.
        if ($field->getType() == 'entity_reference_revisions') {
          $current_values = $block->get($field->getName())->getValue();
          foreach ($current_values as $i => $value) {
            /** @var \Drupal\block_content\Entity\BlockContent $old */
            // Duplicate the items reference.
            $old = $this->entityTypeManager->getStorage('block_content')->loadRevision($value['target_revision_id']);

            // Process the old values.
            $old_values = $old->toArray();
            unset($old_values['id']);
            unset($old_values['uuid']);
            unset($old_values['revision_id']);
            unset($old_values['revision_created']);
            unset($old_values['revision_user']);
            unset($old_values['revision_log']);
            $timestamp = time();
            $date_format = date('mdYHis', $timestamp);
            $old_values['info'] = $old->get('type')->getString() . '_clone_' . $date_format;

            // We have to create one new, if not the reference will be the same.
            $new = $this->entityTypeManager->getStorage('block_content')->create($old_values);
            $new->enforceIsNew();
            $new->save();

            // Store new ids.
            $current_values[$i] = [
              'target_id' => $new->get('id')->getString(),
              'target_revision_id' => $new->get('revision_id')->getString(),
            ];
          }
          $block->set($field->getName(), $current_values);
        }
      }
    }

    $block->save();

    $component['configuration']['block_serialized'] = serialize($block);

    // Store the new uuid.
    $component['configuration']['uuid'] = $new_uuid;

    // Generate the new component with the configuration.
    $new_component = new SectionComponent($new_uuid, $region, $component['configuration']);
    $section_storage->getSection($delta)->appendComponent($new_component);
  }

  /**
   * Clone the column.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage being configured.
   * @param int $delta
   *   The delta.
   * @param string $region
   *   The region.
   * @param array $column
   *   The delta of the section.
   * @param array $old_components
   *   The component.
   */
  public function duplicateColumn(SectionStorageInterface &$section_storage, $delta, $region, array $column, array $old_components) {
    $section = $section_storage->getSection($delta);
    $settings = $section->getLayoutSettings();
    $settings['regions'][$region] = $column;
    $section->setLayoutSettings($settings);
    foreach ($old_components as $component) {
      $this->duplicateBlock($section_storage, $delta, $region, $component);
    }
  }

  /**
   * Clone the section.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage being configured.
   * @param int $delta
   *   The delta.
   * @param \Drupal\layout_builder\Section $section
   *   The section.
   */
  public function duplicateSection(SectionStorageInterface &$section_storage, $delta, Section $section) {
    $new_section = new Section($section->getLayoutId(), $section->getLayoutSettings());
    $section_storage->insertSection($delta, $new_section);
    foreach ($section->getComponents() as $component) {
      $this->duplicateBlock($section_storage, $delta, $component->getRegion(), $component);
    }
  }

  /**
   * Check if the new UUID generated is not in used.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage being configured.
   * @param string $uuid
   *   The delta of the section.
   *
   * @return bool
   *   If it is used or not.
   */
  public function checkUuid(SectionStorageInterface $section_storage = NULL, $uuid = NULL) {
    if (empty($uuid) || !isset($uuid)) {
      return FALSE;
    }

    $sections = $section_storage->getSections();
    foreach ($sections as $section) {
      foreach ($section->getComponents() as $i => $component) {
        if ($i == $uuid) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }

  /**
   * Get default cancel button for LC.
   *
   * @param string $message
   *   The message.
   */
  public function getDefaultCancel($message) {
    $build = [];
    $build['description']['#markup'] = '<div class="layout_builder__add-section-confirm"> ' . $this->t('@message', ['@message' => $message]) . ' </div>';
    return $build;
  }

}

