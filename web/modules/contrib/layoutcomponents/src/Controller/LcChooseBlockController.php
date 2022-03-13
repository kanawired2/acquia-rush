<?php

namespace Drupal\layoutcomponents\Controller;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\layout_builder\Controller\ChooseBlockController;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layoutcomponents\LcDialogHelperTrait;
use Drupal\layoutcomponents\LcLayoutsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Defines a controller to choose a new block type.
 *
 * @internal
 *   Controller classes are internal.
 */
class LcChooseBlockController extends ChooseBlockController {

  use LayoutRebuildTrait;
  use LcDialogHelperTrait;

  /**
   * The section storage.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */

  protected $layoutManager;
  /**
   * The UUID generator.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\SectionStorageInterface
   */
  protected $sectionStorage;

  /**
   * Config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;


  /**
   * Is a default section.
   *
   * @var bool
   */
  protected $isDefault;

  /**
   * LcChooseBlockController constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\layoutcomponents\LcLayoutsManager $layout_manager
   *   The LcLayoutsManager object.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The requestStack.
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The LayoutTempstoreRepositoryInterface object.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The PrivateTempStoreFactory object.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory object.
   */
  public function __construct(BlockManagerInterface $block_manager, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, LcLayoutsManager $layout_manager, UuidInterface $uuid, LayoutTempstoreRepositoryInterface $layout_tempstore_repository, PrivateTempStoreFactory $temp_store, ConfigFactory $config_factory) {
    parent::__construct($block_manager, $entity_type_manager, $current_user);
    $this->layoutManager = $layout_manager;
    $this->uuidGenerator = $uuid;
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->tempStoreFactory = $temp_store;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('uuid'),
      $container->get('layout_builder.tempstore_repository'),
      $container->get('tempstore.private'),
      $container->get('config.factory')
    );
  }

  /**
   * Provides the UI for choosing a new block.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   *   A render array.
   */
  public function build(SectionStorageInterface $section_storage, $delta, $region) {
    // Check section type.
    $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
    $this->isDefault = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;

    // Get temp store lc data.
    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = $this->tempStoreFactory->get('lc');
    $data = $store->get('lc_element');

    /** @var \Drupal\Core\Config\Config $lc_settings */
    $lcSettings = $this->configFactory->getEditable('layoutcomponents.general');

    // If a new element must be copied.
    if (!empty($data)) {
      $this->sectionStorage = $data['section_storage'];
      // Filter by block.
      if ($data['type'] == 'block') {
        // Get the old component.
        $component = $this->sectionStorage->getSection($data['delta'])->getComponent($data['uuid']);
        // Duplicate the block.
        $this->layoutManager->duplicateBlock($section_storage, $delta, $region, $component);
        // Store new.
        $this->layoutTempstoreRepository->set($section_storage);
        // Remove temp data.
        $store->delete('lc_element');
        return $this->rebuildAndClose($section_storage);
      }
    }

    // Normal feature.
    $build = parent::build($section_storage, $delta, $region);

    if ($this->isDefault) {
      $message = 'Is not possible to add a new block in this section because is configured as default';
      $build = $this->layoutManager->getDefaultCancel($message);
      return $build;
    }

    // Categories.
    $un_categories = [
      'Chaos Tools',
      'User fields',
    ];

    // Add class to menu item "Create custom block".
    $build['add_block']['#attributes']['class'][] = 'customblock-menuitem-modal';
    $build['add_block']['#attributes']['data-dialog-options'] = $this->dialogOptions();
    // Alter layoutcomponents blocks names.
    foreach ($build['block_categories'] as $name => $category) {
      // Remove unnecesary categories.
      if (in_array($name, $un_categories)) {
        unset($build['block_categories'][$name]);
        continue;
      }
      // Close category.
      if (is_array($build['block_categories'][$name])) {
        $build['block_categories'][$name]['#open'] = FALSE;
      }
      // Append lc dialog options.
      if (is_array($build['block_categories'][$name])) {
        if (array_key_exists('links', $build['block_categories'][$name])) {
          foreach ($build['block_categories'][$name]['links']['#links'] as $i => $link) {
            $build['block_categories'][$name]['links']['#links'][$i]['attributes']['data-dialog-options'] = $this->dialogOptions();
          }
        }
      }
    }

    $build['#title'] = $this->t('Select a block or create new');

    return $build;
  }

  /**
   * Provides the UI for choosing a new inline block.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   * @param string $region
   *   The region the block is going in.
   *
   * @return array
   *   A render array.
   */
  public function inlineBlockList(SectionStorageInterface $section_storage, $delta, $region) {
    // Parent items.
    $build = parent::inlineBlockList($section_storage, $delta, $region);
    // Block definitions.
    $definitions = $this->blockManager->getFilteredDefinitions('layout_builder', $this->getAvailableContexts($section_storage), [
      'section_storage' => $section_storage,
      'region' => $region,
      'list' => 'inline_blocks',
    ]);
    // Block types.
    $blocks_type = $this->blockManager->getGroupedDefinitions($definitions);
    foreach ($build['links']['#links'] as $key => $link) {
      $blockId = [];
      foreach ($blocks_type['Inline blocks'] as $name => $type) {
        $admin_label = isset($type['admin_label']) ? $type['admin_label'] : NULL;
        $link_title = isset($link['title']) ? $link['title'] : NULL;
        if ($admin_label == $link_title) {
          $blockId = explode(':', $name);
          $build['links']['#links'][$key]['attributes']['class'][] = $blockId[1];
          $build['links']['#links'][$key]['attributes']['data-dialog-options'] = $this->dialogOptions();
        }
      }
      // Remove link if in array.
      if (isset($blockId)) {
        if (in_array('item', explode('_', $blockId[1]))) {
          unset($build['links']['#links'][$key]);
          continue;
        }
      }
    }

    $build['#title'] = $this->t('Select a block type');

    // Add custom selector.
    $build['back_button']['#attributes']['data-drupal-selector'] = 'back';
    $build['back_button']['#attributes']['data-dialog-options'] = $this->dialogOptions();

    return $build;
  }

}

