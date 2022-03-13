<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\Form\RemoveSectionForm;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layoutcomponents\LcLayoutsManager;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;

/**
 * Provides a form for removing section.
 */
class LcRemoveSection extends RemoveSectionForm {

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
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, LcLayoutsManager $layout_manager) {
    parent::__construct($layout_tempstore_repository);
    $this->lcLayoutManager = $layout_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('plugin.manager.layoutcomponents_layouts')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL) {
    // Check section type.
    $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
    $this->isDefault = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;

    $build = parent::buildForm($form, $form_state, $section_storage, $delta);

    if ($this->isDefault) {
      $message = 'This section cannot be deleted because is configurated as default in your display settings';
      $build = $this->lcLayoutManager->getDefaultCancel($message);
    }
    else {
      $build['description']['#markup'] = '<div class="layout_builder__remove-description"> ' . $this->t('This action can not be undone') . ' </div>';
    }

    // Add custom libraries.
    $build['#attached']['library'][] = 'layoutcomponents/layoutcomponents.lateral';

    return $build;
  }

}
