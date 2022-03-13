<?php

namespace Drupal\lc_commands\Commands;

use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\block_content\Entity\BlockContent;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\layoutcomponents\Entity\LcEntityViewDisplay;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplayStorage;
use Drupal\Core\Field\FieldConfigBase;

/**
 * LC commands.
 */

class LcCommands extends DrushCommands {

  /**
   * The folder path.
   *
   * @var string
   */
  protected $folder = DRUPAL_ROOT;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The serializer.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $serializer;

  /**
   * The Config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Inline block usage.
   *
   * @var \Drupal\layout_builder\InlineBlockUsage
   */
  protected $inlineBlockUsage;

  /**
   * LcCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager interface object.
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   *   The serializer interface object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory interface object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, SerializerInterface $serializer, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
    $this->configFactory = $config_factory;
    $this->folder .= $this->configFactory->getEditable('layoutcomponents.general')->get('folder') . '/';
    $this->inlineBlockUsage = \Drupal::service('inline_block.usage');
  }

  /**
   * Delete all content_blocks.
   *
   *
   * @command lc:delete
   * @aliases lcd
   */
  public function delete() {
    $block_storage = $this->entityTypeManager->getStorage('block_content');
    $blocks = $block_storage->loadMultiple();

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    foreach ($blocks as $i => $block) {
      // Provide the las version of block.
      $revision = $block_storage->getLatestRevisionId($block->id());
      $block_revision = $block_storage->loadRevision($revision);

      // Filter by block that they are using by layout.
      if ($block_revision instanceof \Drupal\block_content\Entity\BlockContent) {
        if (!$this->isLcBlock($block_revision, 'delete')) {
          unset($blocks[$i]);
          continue;
        }
      }

      $this->output->writeln('Removing: ' . $block->uuid());
      $block->delete();
    }

    $this->output->writeln( count($blocks) . ' blocks have been deleted');
  }

  /**
   * Export the content_blocks.
   *
   * @command lc:export
   * @aliases lce
   */
  public function export() {
    $blocks = $this->entityTypeManager->getStorage('block_content')->loadMultiple();

    if (!$this->prepareFolder()) {
      return FALSE;
    }

    // Clear the directory.
    $this->clearDirectory();

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    foreach ($blocks as $i => $block) {
      // Ensure that is the last revision.
      $block_revision = $this->getLastRevisionBlock($block->id());

      // Filter by block that they are using by layout.
      if (!$this->isLcBlock($block_revision)) {
        unset($blocks[$i]);
        continue;
      }
      $this->exportBlock($block_revision);
    }

    return TRUE;
  }


  /**
   * Import the content_blocks.
   *
   * @command lc:import
   * @aliases lci
   */
  public function import() {
    // Remove the current blocks.
    $this->delete();

    // Check the directory.
    $files = $this->readDirectory();
    if (empty($files)) {
      $this->output->writeln('The directory is empty');
      return FALSE;
    }

    foreach ($files as $file) {
      $this->output->writeln('Importing: ' . $file);

      /** @var \Drupal\block_content\Entity\BlockContent $n_block */
      $n_block = $this->readFile($file);

      $uuid = $n_block['uuid'][0];

      // Normalize Layout builder field if exists.
      $this->normalizeSections($n_block);

      /** @var \Drupal\block_content\Entity\BlockContent $d_block */
      $d_block = $this->getBlock($uuid);

      // Check for references.
      $references = $n_block['_embedded'];
      if (isset($references)) {
        foreach ($references as $link => $reference) {
          // Get the dependencies already createds.
          $dependencies = $this->getDependencie($n_block, $link);

          // TODO Register the dependencies, the dependencies as media won't be exported.
          /** @var \Drupal\block_content\Entity\BlockContent $dependencie */
          if (!empty($dependencies)) {
            foreach ($dependencies as $i => $dependencie) {
              $n_block[$this->getEmbedded($link)][$i]['target_id'] = $dependencie->id();
              $n_block[$this->getEmbedded($link)][$i]['target_revision_id'] = $dependencie->getRevisionId();
            }
          }
        }
      }

      // Create or update the block.
      if (!empty($d_block)) {
        $d_block->delete();
      }
      $this->createBlock($n_block);
    }

    return TRUE;
  }

  /**
   * Export a block.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   The block.
   * @return bool
   *   If the block has been expoterd.
   */
  public function exportBlock(BlockContent $block) {
    $item = $this->prepareFile($block->toArray());
    if (!$this->writeFile($this->folder . $block->uuid() . '.json', $item)) {
      return FALSE;
    }
    $this->output->writeln('Exporting: ' . $block->uuid());
    return true;
  }

  /**
   * Export the sub-blocks of a block.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   The block.
   */
  public function exportSubBlocks(BlockContent $block) {
    if ($block->hasField('layout_builder__layout')) {
      $layout = $block->get('layout_builder__layout')->getValue();
      if (!empty($layout)) {
        foreach ($layout as $item) {
          /** @var \Drupal\layout_builder\Section $section */
          $section = $item['section'];
          $components = $section->getComponents();
          if (!empty($components)) {
            /** @var \Drupal\layout_builder\SectionComponent $component */
            foreach ($components as $component) {
              /** @var \Drupal\block_content\Entity\BlockContent $sub_block */
              $sub_block = $this->getLastRevisionBlock($component->toArray()['configuration']['block_revision_id']);
              if ($sub_block instanceof \Drupal\block_content\Entity\BlockContent) {
                $this->exportBlock($sub_block);
              }
            }
          }
        }
      }
    }
    else {
      // Find the sub field blocks and import them.
      foreach ($block->getFieldDefinitions() as $field_name => $definition) {
        if ($definition instanceof \Drupal\field\Entity\FieldConfig && $definition->getType() == 'entity_reference_revisions') {
          $this->exportBlock($this->getLastRevisionBlock($block->get($field_name)->getValue()[0]['target_id']));
        }
      }
    }
  }

  /**
   * Create the block and store the rest of translates.
   *
   * @param array $n_block
   *   array block.
   */
  public function createBlock(array $n_block) {
    // Get the langcodes.
    $langcodes = $n_block['langcode'];
    // Store the default block.
    BlockContent::create($n_block)->save();
    // Get the new block.
    $block = $this->getBlock($n_block['uuid'][0]['value']);
    // If the block contains more than 1 language.
    if (count($langcodes) > 1) {
      // Store each language.
      foreach ($langcodes as $langcode) {
        // Filter by non-default language.
        if ($block->language()->getId() !== $langcode['lang']) {
          $block_translate = [];
          // Find the fields.
          foreach ($block->getFieldDefinitions() as $definition) {
            if ($definition instanceof FieldConfigBase) {
              $field_name  = $definition->get('field_name');
              // Find the value by language.
              $field_translate = $n_block[$field_name];
              if (empty($field_translate)) {
                continue;
              }
              foreach ($field_translate as $i => $value) {
                if ($value['lang'] == $langcode['lang']) {
                  $block_translate[$field_name] = $n_block[$field_name][$i];
                }
              }
            }
          }
          // Store the new translation.
          $block->addTranslation($langcode['lang'], $block_translate)->save();
        }
      }
    }
  }

  /**
   * Check if the block is a LC block and is used from a display.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   The block.
   * @return bool
   *   If is a LC block.
   */
  public function isLcBlock(BlockContent $block, $action = 'export') {
    /** @var LayoutBuilderEntityViewDisplayStorage $storage */
    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    $displays = $storage->loadMultiple();

    foreach ($displays as $name => $display) {
      // Filter by LcEntityViewDisplay entity.
      if ($display instanceof LcEntityViewDisplay) {
        foreach ($display->getSections() as $section) {
          $components = $section->getComponents();
          if (!empty($components)) {
            foreach ($components as $name => $component) {
              $configuration = $component->get('configuration');
              // Compare the label and block revision id.
              if (!empty($configuration['label'])) {
                if ($configuration['label'] == $block->get('info')->getString() && $configuration['block_revision_id'] == $block->getRevisionId()) {
                  // Entity reference revisions validation.
                  // Find the entity_reference_revisions fields.
                  $definitions = $block->getFieldDefinitions();
                  foreach ($definitions as $definition) {
                    if ($definition->getType() == 'entity_reference_revisions') {
                      /** @var \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList $list */
                      $list = $block->get($definition->getName());
                      for ($i = 0; $i < $list->count(); $i++) {
                        /** @var \Drupal\block_content\Entity\BlockContent $tab_item */
                        $tab_item = $this->getLastRevisionBlock($list->get($i)->getValue()['target_id']);
                        if (!isset($tab_item)) {
                          continue;
                        }

                        if ($action == 'delete') {
                          $tab_item->delete();
                          $this->writeln('Deleting: ' . $tab_item->uuid());
                        }
                        else {
                          // Export the entity_reference_revisions block.
                          $this->exportBlock($tab_item);

                          // Export the sub entity_reference_revisions blocks.
                          $this->exportSubBlocks($tab_item);
                        }
                      }
                    }
                  }
                  return TRUE;
                }
              }
            }
          }
        }
      }
    }
    // Block not found in any display.
    return FALSE;
  }

  /**
   * Get the dependencie or create if not exists.
   *
   * @param \Drupal\block_content\Entity\BlockContent $n_block
   *   The block.
   * @param string $reference
   *   The new reference.
   * @return array
   *   The array of dependencies.
   */
  public function getDependencie(&$n_block, $reference) {
    $dependencies = [];
    // Get the embed reference.
    $embed = $this->getEmbedded($reference);
    if (!empty($embed) && strpos($embed, 'field') !== FALSE) {
      foreach ($n_block['_embedded'][$reference] as $i => $dependencie) {
        $n_uuid = $n_block['_embedded'][$reference][$i]['uuid'][0]['value'];
        // Check if the file xists.
        if (!file_exists($this->folder . $n_uuid . '.json')) {
          return null;
        }
        // Get the reference block.
        $new_block = $this->readFile($n_uuid . '.json');
        $uuid = $new_block['uuid'][0];

        // Get the current block.
        $block = $this->getBlock($uuid);

        if (empty($block)) {
          // Normalize Layout builder field if exists.
          $this->normalizeSections($new_block);

          // Create if not exists.
          BlockContent::create($new_block)->save();
          $dependencies[$i] = $this->getBlock($uuid);
        }
        else {
          // Update the block with the new data.
          $dependencies[$i] = $this->updateDependencie($block, $n_uuid);
        }
      }
    }
    return $dependencies;
  }

  /**
   * Get the decoded file content.
   *
   * @param string $file
   *   The file.
   * @return string
   *   The content.
   */
  public function readFile($file) {
    return $this->serializer->decode($this->parseFile($file), 'hal_json');
  }

  /**
   * Get the current block.
   *
   * @param string $uuid
   *   The uuid.
   * @return \Drupal\block_content\Entity\BlockContent
   *   The new block.
   */
  public function getBlock($uuid) {
    $block = $this->entityTypeManager
      ->getStorage('block_content')
      ->loadByProperties(['uuid' => $uuid]);

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = reset($block);

    return $block;
  }

  /**
   * Get the block by last revision.
   *
   * @param string $id
   *   The block id.
   * @return \Drupal\block_content\Entity\BlockContent
   *   The full block.
   */
  public function getLastRevisionBlock($id) {
    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $storage */
    $storage = $this->entityTypeManager->getStorage('block_content');
    $revision = $storage->getLatestRevisionId($id);
    return $storage->loadRevision($revision);
  }

  /**
   * Update the dependencie of the block.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   The block.
   * @param string $uuid
   *   The uuid.
   * @return \Drupal\block_content\Entity\BlockContent
   *   The new block.
   */
  public function updateDependencie($block, $uuid) {
    $new_block = $this->readFile($uuid . '.json');
    return $this->updateBlock($block, $new_block);
  }

  /**
   * Get the uuid of the file.
   *
   * @param  string $link
   *   The uuid file.
   * @return string
   *   The string.
   */
  public function getEmbedded($link) {
    $parts = explode('/', $link);
    return $parts[count($parts) - 1];
  }

  /**
   * Update each block with the new data.
   *
   * @return \Drupal\block_content\Entity\BlockContent
   *   The new block.
   */
  public function updateBlock($block, $n_block) {
    foreach ($block->getFields() as $name => $field) {
      $value = $n_block[$name];
      $block->set($name, $value);
    }

    $block->save();
    return $block;
  }

  /**
   * Check the folder.
   *
   * @return bool
   *   If exists and is writable.
   */
  public function prepareFolder() {
    if (!is_dir($this->folder)) {
      $this->output->writeln('The folder not exists');
      return FALSE;
    }

    if (!is_writable($this->folder)) {
      $this->output->writeln('The folder is not writabble');
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Serialize the file content.
   *
   * @return string
   *   The file content serialized.
   */
  public function prepareFile($item) {
    // Normalize the Layout builder field.
    if (array_key_exists('layout_builder__layout', $item)) {
      $new = $item;
      $new['layout_builder__layout'] = $this->prepareSections($item['layout_builder__layout']);
      $item = $new;
    }
    return $this->serializer->serialize($item, 'hal_json', ['json_encode_options' => 128]);
  }

  /**
   * Prepare the sections to import them.
   *
   * @param array $sections
   *   The array of sections.
   * @return array
   *   The array converted.
   */
  public function prepareSections(array $sections) {
    $json_sections = [];
    foreach ($sections as $key => $section) {
      $json_sections[$key]['section'] = $this->prepareFile($section['section']->toArray());
    }
    return $json_sections;
  }

  /**
   * Normalize the sections for layout builder fields.
   *
   * @param array $block
   *   The block as array.
   */
  public function normalizeSections(&$block) {
    if (array_key_exists('layout_builder__layout', $block)) {
      // Normalize Layout builder value.
      foreach ($block['layout_builder__layout'] as $key => $section) {
        $data = $this->serializer->decode($section['section'], 'hal_json');
        $components = [];
        // Register the components.
        foreach ($data['components'] as $uuid => $component) {
          $components[$uuid] = new SectionComponent($component['uuid'], $component['region'], $component['configuration'], $component['additional']);
        }
        // Generate the section.
        $layout_builder[$key] = new Section($data['layout_id'], $data['layout_settings'], $components, []);
      }
      $block['layout_builder__layout'] = $layout_builder;
    }
  }

  /**
   * Write the file.
   *
   * @return bool
   *   If the file has been written correctly.
   */
  public function writeFile($file, $item) {
    if (!file_put_contents($file, $item)) {
      $this->output->writeln('An error encoured writing the file');
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Get the file content.
   *
   * @return string|false
   *   The function returns the read data or false on failure.
   */
  protected function parseFile($file) {
    return file_get_contents($this->folder . $file);
  }

  /**
   * Read the files of directory.
   *
   * @return array
   *   The array with the files.
   */
  public function readDirectory() {
    return array_diff(scandir($this->folder), array('..', '.'));
  }

  /**
   * Remove the files of directory.
   */
  public function clearDirectory() {
    $files = $this->readDirectory();
    foreach ($files as $file) {
      unlink($this->folder . $file);
    }
  }

}
