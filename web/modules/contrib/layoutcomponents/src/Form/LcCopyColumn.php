<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\Plugin\Block\InlineBlock;
use Drupal\layoutcomponents\Form\LcCopy as LcCopy;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Provides a form to copy a column.
 */
class LcCopyColumn extends LcCopy {

  use AjaxFormHelperTrait;
  use LayoutRebuildTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_copy_column';
  }

  /**
   * Provides the UI for copy a column.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
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
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $region = NULL) {
    $this->sectionStorage = $section_storage;
    $this->delta = $delta;
    $this->region = $region;
    $this->type = 'column';
    $section = $section_storage->getSection($delta);

    foreach ($section->getComponentsByRegion($region) as $component) {
      if (!$component->getPlugin() instanceof InlineBlock) {
        $form['markup'] = [
          '#type' => 'markup',
          '#markup' => '<div class="layout_builder__add-section-confirm">' . $this->t('This column cannot be cloned because contains some unsupported block!') . '</div>',
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
        'region' => $this->region,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $response = parent::successfulAjaxSubmit($form, $form_state);
    if (!$this->getDefault()) {
      $response->addCommand(new InvokeCommand('a[class*="layout-builder__column_link-configure"]', 'addClass', ['lc-copy']));
      $response->addCommand(new InvokeCommand('a[class$="layout-builder__column_link-copy"]', 'addClass', ['hidden']));
    }
    return $response;
  }

}
