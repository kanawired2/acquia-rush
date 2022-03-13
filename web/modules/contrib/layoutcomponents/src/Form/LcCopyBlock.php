<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\layoutcomponents\Form\LcCopy as LcCopy;
use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder\Plugin\Block\InlineBlock;

/**
 * Provides a form to copy a block.
 */
class LcCopyBlock extends LcCopy {

  use AjaxFormHelperTrait;
  use LayoutRebuildTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_copy_block';
  }

  /**
   * Provides the UI for copy a block.
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
   * @param string $uuid
   *   The uuid of the block.
   *
   * @return array|\Drupal\Core\Ajax\AjaxResponse
   *   A render array.
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $region = NULL, $uuid = NULL) {
    $component = $section_storage->getSection($delta)->getComponent($uuid);
    $this->sectionStorage = $section_storage;
    $this->delta = $delta;
    $this->region = $region;
    $this->uuid = $component->getUuid();
    $this->type = 'block';

    if (!$component->getPlugin() instanceof InlineBlock) {
      $form['markup'] = [
        '#type' => 'markup',
        '#markup' => '<div class="layout_builder__add-section-confirm">' . $this->t('This block cannot be cloned because is an unsupported block!') . '</div>',
      ];
    }
    else {
      $form = parent::buildForm($form, $form_state);
    }

    return $form;
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
        'uuid' => $this->uuid,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $response = parent::successfulAjaxSubmit($form, $form_state);
    if (!$this->getDefault()) {
      $response->addCommand(new InvokeCommand('a[class*="layout-builder__column_link-add"]', 'addClass', ['lc-copy']));
    }
    return $response;
  }

}
