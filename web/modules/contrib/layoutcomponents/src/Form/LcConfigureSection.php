<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\Form\ConfigureSectionForm;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder\Section;
use Drupal\layoutcomponents\LcSectionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\layoutcomponents\LcLayoutsManager;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Provides a form for configuring a layout section.
 *
 * @internal
 *   Form classes are internal.
 */
class LcConfigureSection extends ConfigureSectionForm {

  use AjaxFormHelperTrait;
  use LayoutRebuildTrait;

  /**
   * RequestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The LC manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $lcLayoutManager;

  /**
   * Drupal\layoutcomponents\LcSectionManager definition.
   *
   * @var \Drupal\layoutcomponents\LcSectionManager
   */
  protected $lcSectionManager;

  /**
   * Is a default section.
   *
   * @var bool
   */
  protected $isDefault;

  /**
   * {@inheritdoc}
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, PluginFormFactoryInterface $plugin_form_manager, RequestStack $request, LcLayoutsManager $layout_manager, LcSectionManager $lc_section_manager) {
    parent::__construct($layout_tempstore_repository, $plugin_form_manager);
    $this->request = $request;
    $this->lcLayoutManager = $layout_manager;
    $this->lcSectionManager = $lc_section_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('plugin_form.factory'),
      $container->get('request_stack'),
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('layoutcomponents.section')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $plugin_id = NULL) {
    $this->isDefault = 0;
    // Check section type.
    try {
      $section = $section_storage->getSection($delta)->getLayoutSettings();
      if (array_key_exists('section', $section)) {
        $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
        $this->isDefault = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;
      }
    }
    catch (\Exception $e) {
      $this->isDefault = 0;
    }

    // Get custom params.
    $update_layout = $this->request->getCurrentRequest()->query->get('update_layout');
    $autosave = $this->request->getCurrentRequest()->query->get('autosave');
    $sub_section = $this->request->getCurrentRequest()->query->get('sub_section');

    $form_state->setValue('sub_section', $sub_section);

    // Do we need update the layout?
    if (boolval($update_layout)) {
      // Old Section.
      $section = $section_storage->getSection($delta);

      // Store old components.
      $components = $section->getComponents();

      // All componentes should be in first region.
      foreach ($components as $key => $component) {
        $component->set('region', 'first');
      }

      // Store old layout settings.
      $layoutSettings = $section->getLayoutSettings();

      // New section with old values.
      $newSection = new Section($plugin_id, $layoutSettings, $components);

      // Remove old section to not get conflicts.
      $section_storage->removeSection($delta);

      // Register new section in SectionStorage $section_storage.
      $section_storage->insertSection($delta, $newSection);

      // Remove plugin id to parent form detect new section as old section.
      $plugin_id = NULL;
    }

    $build = parent::buildForm($form, $form_state, $section_storage, $delta, $plugin_id);

    if ($this->isDefault && !boolval($autosave)) {
      // This section cannot be configured.
      $message = 'This section cannot be configured because is configurated as default';
      $build = $this->lcLayoutManager->getDefaultCancel($message);
    }
    else {
      // Add new step if is new section or is a update.
      if (boolval($autosave)) {
        $build['new_section'] = [
          '#type' => 'help',
          '#markup' => '<div class="layout_builder__add-section-confirm">' . $this->t("Are you sure to add a new section?") . '</div>',
          '#weight' => -1,
        ];

        if (boolval($update_layout)) {
          $build['new_section']['#markup'] = '<div class="layout_builder__add-section-confirm">' . $this->t("Are you sure to change layout?") . '</div>';
        }

        $build['layout_settings']['container']['#prefix'] = '<div class="lc-lateral-container hidden">';
        $build['layout_settings']['container']['#suffix'] = '</div>';
      }
    }

    // Hidde other configurations.
    $build['layout_settings']['container']['regions']['#access'] = FALSE;
    $build['layout_settings']['container']['section']['#open'] = TRUE;

    $build['layout_settings']['container']['section']['sub_section'] = $sub_section;
    $build['sub_section_parent_section'] = [
      '#type' => 'hidden',
      '#default_value' => $sub_section['parent_section'],
    ];
    $build['sub_section_parent_region'] = [
      '#type' => 'hidden',
      '#default_value' => $sub_section['parent_region'],
    ];

    return $build;
  }

  /**
   * Custom submit form to include sub section configuration.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Call the plugin submit handler.
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $this->getPluginForm($this->layout)->submitConfigurationForm($form['layout_settings'], $subform_state);

    $plugin_id = $this->layout->getPluginId();
    $configuration = $this->layout->getConfiguration();


    if ($this->isUpdate) {
      $this->sectionStorage->getSection($this->delta)->setLayoutSettings($configuration);
    }
    else {
      // Include the new sub section.
      $parent_section = $form_state->getValue('sub_section_parent_section');
      $parent_region = $form_state->getValue('sub_section_parent_region');

      if (is_numeric($parent_section) && !empty($parent_region)) {
        $dd_settings = $this->lcSectionManager->getLayoutSettings($this->sectionStorage, $this->delta);
        $new_uuid = \Drupal::service('uuid')->generate();
        if (!array_key_exists('lc_id', $dd_settings)) {
          $dd_settings['lc_id'] = $new_uuid;

          // If current parent section hasn't id, add new.
          $this->sectionStorage->getSection($this->delta)->setLayoutSettings($dd_settings);
        }
        else {
          $new_uuid = $dd_settings['lc_id'];
        }

        // Provide the sub section configuration.
        $configuration['sub_section'] = [
          'lc_id' => $new_uuid,
          'parent_section' => $parent_section,
          'parent_region' => $parent_region,
        ];

      }

      // Register the sub section.
      $this->sectionStorage->insertSection($this->delta, new Section($plugin_id, $configuration));
    }

    $this->layoutTempstoreRepository->set($this->sectionStorage);
    $form_state->setRedirectUrl($this->sectionStorage->getLayoutBuilderUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutSettings() {
    return $this->layout;
  }

}
