<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\Form\UpdateBlockForm;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\layout_builder\SectionComponent;
use Drupal\layoutcomponents\LcLayoutsManager;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;
use Drupal\block_content\Entity\BlockContent;

/**
 * Provides a form to update a block.
 */
class LcUpdateBlockForm extends UpdateBlockForm {

  use AjaxFormHelperTrait;
  use LayoutRebuildTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The LC manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $lcLayoutManager;

  /**
   * Is a default section.
   *
   * @var bool
   */
  protected $isDefault;

  /**
   * {@inheritdoc}
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, ContextRepositoryInterface $context_repository, BlockManagerInterface $block_manager, UuidInterface $uuid, PluginFormFactoryInterface $plugin_form_manager, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, LcLayoutsManager $layout_manager) {
    parent::__construct($layout_tempstore_repository, $context_repository, $block_manager, $uuid, $plugin_form_manager);
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->lcLayoutManager = $layout_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('context.repository'),
      $container->get('plugin.manager.block'),
      $container->get('uuid'),
      $container->get('plugin_form.factory'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('plugin.manager.layoutcomponents_layouts')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $region = NULL, $uuid = NULL) {
    // Check section type.
    $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
    $this->isDefault = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;

    /** @var \Drupal\layout_builder\SectionComponent $component */
    $component = $section_storage->getSection($delta)->getComponent($uuid);
    $conf = $component->get('configuration');

    // Transform the block if is a block_content.
    if (strpos($conf['id'], 'block_content:') > -1) {
      $component->setConfiguration($this->blockContentToInline($conf));
    }

    // Ensure get the current language translation.
    //$this->setCurrentLanguageTranslation($component);

    $build = parent::buildForm($form, $form_state, $section_storage, $delta, $region, $uuid);

    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = !empty($build['settings']['block_form']['#block'])
      ? $build['settings']['block_form']['#block'] : NULL;
    if (!isset($block)) {
      // Format non blocks.
      foreach ($build['settings'] as $name => $element) {
        if (array_key_exists('#type', $element)) {
          $layout = \Drupal::service('layoutcomponents.apiComponent');
          $item = $build['settings'][$name];
          $build['settings'][$name] = $layout->getComponentElement(
            [
              'no_lc' => TRUE,
            ],
            $item
          );
        }
      }
    }

    if (array_key_exists('block_form', $build['settings'])) {
      $build['#title'] = $this->t("Edit @title", ['@title' => $block->get("info")->getString()]);
    }
    else {
      $build['#title'] = $this->t("Edit field @title", ['@title' => $build['settings']['admin_label']['#plain_text']]);
    }

    if ($this->isDefault) {
      $message = 'This block cannot be updated because is configurated as default in your display settings';
      $build['description']['#markup'] = '<div class="layout_builder__add-section-confirm"> ' . $this->t('@message', ['@message' => $message]) . ' </div>';
      $build['description']['#weight'] = -1;
      $build['settings']['block_form']['#access'] = FALSE;
      $build['settings']['formatter']['#access'] = FALSE;
      unset($build['actions']['submit']);
    }

    // Hidde block config.
    $build['settings']['admin_label']['#access'] = FALSE;

    return $build;
  }

  /**
   * Trnasform a block content to inline for LC.
   *
   * @param array $configuration
   *   The array with the configuration.
   *
   * @return array
   *   The new configuration.
   */
  public function blockContentToInline(array $configuration) {
    $configuration['id'] = str_replace('block_content:', '', $configuration['id']);
    /** @var \Drupal\block_content\Entity\BlockContent $block_content */
    $block_content = $this->entityTypeManager->getStorage('block_content')->loadByProperties(['uuid' => $configuration['id']]);
    $block_content = reset($block_content);
    return [
      'id' => 'inline_block:' . $block_content->get('type')->getString(),
      'label' => $configuration['label'],
      'provider' => 'layout_builder',
      'block_serialized' => serialize($block_content),
      'label_display' => FALSE,
      'status' => TRUE,
      'info' => '',
      'view_mode' => 'full',
      'context_mapping' => [],
    ];
  }

  /**
   * Set translation of current language.
   *
   * @param \Drupal\layout_builder\SectionComponent $component
   *   The layout builder component.
   */
  public function setCurrentLanguageTranslation(SectionComponent &$component) {
    $configuration = $component->get('configuration');;
    try {
      /** @var \Drupal\block_content\Entity\BlockContent $block_content */
      $block_content = $component->getPlugin()->build();
      $block_content = reset($block_content);
      if (!$block_content instanceof BlockContent) {
        return;
      }

      // Ensure that is default revision.
      // This only work if the block type has checked the revisions.
      $block_content->isDefaultRevision(TRUE);

      if (!$block_content->hasTranslation($this->languageManager->getCurrentLanguage()->getId())) {
        $block_content->addTranslation($this->languageManager->getCurrentLanguage()->getId(), $block_content->getFields());
      }

      $configuration['block_serialized'] = serialize($block_content->getTranslation($this->languageManager->getCurrentLanguage()->getId()));
      $component->setConfiguration($configuration);
    }
    catch (\Exception $e) {
      return;
    }
  }

}
