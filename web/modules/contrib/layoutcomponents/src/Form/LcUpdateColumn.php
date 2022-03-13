<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\layout_builder\Form\ConfigureSectionForm;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layoutcomponents\LcLayoutsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Provides a form for configuring section.
 */
class LcUpdateColumn extends ConfigureSectionForm {

  /**
   * The section storage.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $layoutManager;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

  /**
   * Is a default section.
   *
   * @var bool
   */
  protected $isDefault;

  /**
   * Constructs to update the column.
   *
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\Core\Plugin\PluginFormFactoryInterface $plugin_form_manager
   *   The plugin form manager.
   * @param \Drupal\layoutcomponents\LcLayoutsManager $layout_manager
   *   The LcLayoutsManager object.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The PrivateTempStoreFactory object.
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, PluginFormFactoryInterface $plugin_form_manager, LcLayoutsManager $layout_manager, PrivateTempStoreFactory $temp_store) {
    parent::__construct($layout_tempstore_repository, $plugin_form_manager);
    $this->layoutManager = $layout_manager;
    $this->tempStoreFactory = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('plugin_form.factory'),
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $plugin_id = NULL, $region = NULL) {
    // Check section type.
    $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
    $this->isDefault = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;

    // Get temp store lc data.
    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = $this->tempStoreFactory->get('lc');
    $data = $store->get('lc_element');

    if (!empty($data)) {
      if ($data['type'] == 'column') {
        $column = $data['section_storage']->getSection($data['delta'])->getLayoutSettings()['regions'][$data['region']];
        $old_components = $data['section_storage']->getSection($data['delta'])->getComponentsByRegion($data['region']);
        $this->layoutManager->duplicateColumn($section_storage, $delta, $region, $column, $old_components);
        // Store new.
        $this->layoutTempstoreRepository->set($section_storage);
        // Remove temp data.
        $store->delete('lc_element');
        return $this->rebuildAndClose($section_storage);
      }
    }

    $build = parent::buildForm($form, $form_state, $section_storage, $delta, $plugin_id);

    if ($this->isDefault) {
      $message = 'This column cannot be deleted because is configurated as default in your display settings';
      $build = $this->layoutManager->getDefaultCancel($message);
      return $build;
    }

    $section = $section_storage->getSection($delta);

    // Hide section form.
    $build['layout_settings']['container']['title']['#access'] = FALSE;
    $build['layout_settings']['container']['section']['#access'] = FALSE;

    // Change form title.
    $form['#title'] = $this->t('Configure column');

    // Hide others columns.
    $column_settings = &$build['layout_settings']['container']['regions'];
    $column_settings['#open'] = TRUE;

    // Proccess columns.
    $regions = $section->getLayout()->getPluginDefinition()->getRegionNames();
    foreach ($regions as $key => $name) {
      // Expand columns.
      $column_settings[$name]['#open'] = TRUE;
      if ($region !== $name) {
        $column_settings[$name]['#access'] = FALSE;
      }
    }

    // Set custom classes.
    $build['layout_settings']['container']['#attributes'] = [
      'class' => ['lc-container-column'],
    ];

    // Add custom libraries.
    $build['#attached']['library'][] = 'layoutcomponents/layoutcomponents.lateral-column';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutSettings() {
    return $this->layout;
  }

}
