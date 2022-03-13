<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\layout_builder\Form\MoveSectionsForm;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\Plugin\SectionStorage\DefaultsSectionStorage;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layoutcomponents\LcSectionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a form for moving a section.
 *
 * @internal
 *   Form classes are internal.
 */
class LcMoveSections extends MoveSectionsForm {

  use StringTranslationTrait;

  /**
   * RequestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Drupal\layoutcomponents\LcSectionManager definition.
   *
   * @var \Drupal\layoutcomponents\LcSectionManager
   */
  protected $lcSectionManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, RequestStack $request, LcSectionManager $lc_section_manager) {
    parent::__construct($layout_tempstore_repository);
    $this->request = $request;
    $this->lcSectionManager = $lc_section_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('request_stack'),
      $container->get('layoutcomponents.section')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL) {
    $build = parent::buildForm($form, $form_state, $section_storage);

    // Sub section handler.
    $sub_delta = $this->request->getCurrentRequest()->query->get('sub_section');

    foreach ($build['sections_wrapper']['sections'] as $delta => $wrapper) {
      if (!is_numeric($delta)) {
        continue;
      }
      // Check section type.
      $section_overwrite = $section_storage->getSection($delta)->getLayoutSettings()['section']['general']['basic']['section_overwrite'];
      $is_default = (boolval($section_overwrite) && !$section_storage instanceof DefaultsSectionStorage) ? TRUE : FALSE;
      $build['sections_wrapper']['warning']['#markup'] = '<div class="layout_builder__add-section-confirm"> ' . $this->t('* Default sections cannot be moved, they always will keep the same position. If you want a free to reorder, you must disable the overwriting in your display settings.') . ' </div>';
      if ($is_default) {
        $build['sections_wrapper']['sections'][$delta]['#attributes']['class'][0] = 'disabled';
        $build['sections_wrapper']['sections'][$delta]['label']['#markup'] = $this->t('Default Section @section', ['@section' => $delta + 1]);
      }

      if (is_array($sub_delta)) {
        $sub_settings = $this->lcSectionManager->getLayoutSettings($section_storage, $sub_delta['delta']);
        $current_settings = $this->lcSectionManager->getLayoutSettings($section_storage, $delta);

        // If is a sub section, hidde the rest of normal sections.
        $sub_id = $this->lcSectionManager->getLcId($section_storage, $sub_delta['delta']);

        if ($this->lcSectionManager->getLcId($section_storage, $delta) != $sub_id || $sub_settings['sub_section']['parent_region'] !== $current_settings['sub_section']['parent_region']) {
          // We can only hidde the elements, if we remove them, LB will remove the rest of sections.
          $build['sections_wrapper']['sections'][$delta]['#attributes']['class'][0] = 'hidden';
        }
        $build['sections_wrapper']['warning']['#markup'] = '<div class="layout_builder__add-section-confirm"> ' . $this->t('* Your are editing the position of sub sections included in a region, so they will only appear available to move the sub sections included in the parent section.') . ' </div>';
      }
      else {
        // If isn't a sub section, then hidde sub sections.
        if ($this->lcSectionManager->isSubSection($section_storage, $delta)) {
          $build['sections_wrapper']['sections'][$delta]['#attributes']['class'][0] = 'hidden';
        }
      }
    }

    return $build;
  }

}
