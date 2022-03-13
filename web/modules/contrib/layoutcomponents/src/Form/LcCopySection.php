<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\layoutcomponents\Form\LcCopy as LcCopy;
use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Provides a form to copy a section.
 */
class LcCopySection extends LcCopy {

  use AjaxFormHelperTrait;
  use LayoutRebuildTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_copy_section';
  }

  /**
   * Provides the UI for copy a section.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage.
   * @param int $delta
   *   The delta of the section to splice.
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   *   A render array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL) {
    $this->sectionStorage = $section_storage;
    $this->delta = $delta;
    $this->type = 'section';
    $section = $section_storage->getSection($delta);

    foreach ($section->getComponents() as $component) {
      if (!$component->getPlugin() instanceof InlineBlock) {
        $form['markup'] = [
          '#type' => 'markup',
          '#markup' => '<div class="layout_builder__add-section-confirm">' . $this->t('This section cannot be cloned because contains some unsupported block!') . '</div>',
        ];
        return $form;
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!$this->getDefault()) {
      /** @var \Drupal\Core\TempStore\PrivateTempStore $store */
      $store = $this->tempStoreFactory->get('lc');
      $store->set('lc_element', [
        'type' => $this->type,
        'section_storage' => $this->sectionStorage,
        'delta' => $this->delta,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $response = parent::successfulAjaxSubmit($form, $form_state);
    if (!$this->getDefault()) {
      $response->addCommand(new InvokeCommand('a[class*="layout-builder__link-add-section"]', 'addClass', ['lc-copy']));
    }
    return $response;
  }

}
