<?php

namespace Drupal\layoutcomponents\Controller;

use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\layout_builder\Controller\ChooseSectionController;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\Core\Url;
use Drupal\layoutcomponents\LcDialogHelperTrait;
use Drupal\layoutcomponents\LcLayoutsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;
use Drupal\Core\Config\ConfigFactory;

/**
 * Defines a controller to add a new section.
 *
 * @internal
 *   Controller classes are internal.
 */
class LcChooseSectionController extends ChooseSectionController {

  use LayoutRebuildTrait;
  use LcDialogHelperTrait;

  /**
   * RequestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * The LC manager.
   *
   * @var \Drupal\layoutcomponents\LcLayoutsManager
   */
  protected $lcLayoutManager;

  /**
   * Drupal\Core\TempStore\PrivateTempStoreFactory definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private $tempStoreFactory;

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
   * LcChooseSectionController constructor.
   *
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_manager
   *   The layout manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The requestStack.
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The LayoutTempstoreRepositoryInterface object.
   * @param \Drupal\layoutcomponents\LcLayoutsManager $lc_layout_manager
   *   The LcLayoutsManager object.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The PrivateTempStoreFactory object.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory object.
   */
  public function __construct(LayoutPluginManagerInterface $layout_manager, RequestStack $request, LayoutTempstoreRepositoryInterface $layout_tempstore_repository, LcLayoutsManager $lc_layout_manager, PrivateTempStoreFactory $temp_store, ConfigFactory $config_factory) {
    parent::__construct($layout_manager);
    $this->request = $request;
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->lcLayoutManager = $lc_layout_manager;
    $this->tempStoreFactory = $temp_store;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.core.layout'),
      $container->get('request_stack'),
      $container->get('layout_builder.tempstore_repository'),
      $container->get('plugin.manager.layoutcomponents_layouts'),
      $container->get('tempstore.private'),
      $container->get('config.factory')
    );
  }

  /**
   * Adds the new section.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   *
   * @return array
   *   The render array.
   */
  public function build(SectionStorageInterface $section_storage, $delta) {
    // Get temp store lc data.
    /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
    $store = $this->tempStoreFactory->get('lc');
    $data = $store->get('lc_element');

    if (!empty($data)) {
      if ($data['type'] == 'section') {
        $section = $data['section_storage']->getSection($data['delta']);
        $this->lcLayoutManager->duplicateSection($section_storage, $delta, $section);
        // Store new.
        $this->layoutTempstoreRepository->set($section_storage);
        // Remove temp data.
        $store->delete('lc_element');
        return $this->rebuildAndClose($section_storage);
      }
    }

    // Get update layout param.
    $updateLayout = $this->request->getCurrentRequest()->query->get('update_layout');

    // Get sub section param.
    $subSection = $this->request->getCurrentRequest()->query->get('sub_section');

    $build = parent::build($section_storage, $delta);

    if ($updateLayout) {
      // If is a change of structure check the section type.
      $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
      $this->isDefault = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;

      if ($this->isDefault) {
        // This section cannot be configured.
        $message = 'The structure of this section cannot be changed because this section is configurated as default';
        $build = $this->lcLayoutManager->getDefaultCancel($message);
        return $build;
      }
    }
    else {
      if (!$section_storage instanceof DefaultsSectionStorage) {
        $build['description'] = [
          '#markup' => '<div class="layout_builder__add-section-confirm"> ' . $this->t('The new section will be added on the position depending on the configuration of your display') . ' </div>',
          '#weight' => -1,
        ];
      }
    }

    $layoutcomponents = [];
    $others = [];
    $plugin_id = '';

    foreach ($build['layouts']['#items'] as $key => $item) {
      if (isset($item['#title']['#attributes'])) {
        $classes = $item['#title']['#attributes']['class'][1];
      }
      else {
        $classes = [];
      }
      /** @var \Drupal\Core\Url $url */
      $url = $item['#url'];
      $item['#url'] = Url::fromRoute('layout_builder.configure_section',
        [
          'section_storage_type' => $section_storage->getStorageType(),
          'section_storage' => $section_storage->getStorageId(),
          'delta' => $delta,
          'plugin_id' => $url->getRouteParameters()['plugin_id'],
          'update_layout' => $updateLayout,
          'autosave' => 1,
          'sub_section' => $subSection,
        ]);
      $item['#attributes']['data-dialog-options'] = $this->dialogOptions();;

      $plugin_id = $url->getRouteParameters()['plugin_id'];
      if (array_key_exists('plugin_id', $url->getRouteParameters())) {
        $plugin_id = $url->getRouteParameters()['plugin_id'];
        if (strpos($plugin_id, 'layoutcomponents_') !== FALSE) {
          $layoutcomponents[] = $item;
        }
        else {
          $others[] = $item;
        }
      }
    }
    // $output['layouts']['#items'] = array_merge($layoutcomponents, $others);.
    // Only layoutcomponents layouts.
    $build['layouts']['#items'] = $layoutcomponents;


    return $build;
  }

}
