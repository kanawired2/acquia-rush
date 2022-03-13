<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\Form\AddBlockForm;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Provides a form to add a block.
 */
class LcAddBlockForm extends AddBlockForm {

  use AjaxHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $region = NULL, $plugin_id = NULL) {
    $build = parent::buildForm($form, $form_state, $section_storage, $delta, $region, $plugin_id);

    $admin_label = isset($build['settings']['admin_label']) ? $build['settings']['admin_label'] : NULL;

    if (array_key_exists('block_form', $build['settings'])) {
      /** @var \Drupal\block_content\Entity\BlockContent $block */
      $block = $build['settings']['block_form']['#block'];
      $build['#title'] = $this->t("Add new @title", ['@title' => $block->bundle()]);
    }
    else {
      $admin_label_plain_text = '';
      if (!empty($admin_label)) {
        $admin_label_plain_text = isset($admin_label['#plain_text']) ? $admin_label['#plain_text'] : '';
      }
      $build['#title'] = $this->t("Add field @title", ['@title' => $admin_label_plain_text]);
    }

    // Title and description config.
    if (!empty($admin_label)) {
      $build['settings']['admin_label']['#access'] = FALSE;
    }
    $build['settings']['label']['#title'] = '<span class="lc-lateral-title">' . $this->t("Title") . '</span>' . '<span class="lc-lateral-info" title="' . $this->t("Set an identifier of this block") . '"/>';
    unset($build['settings']['label']['#description']);

    // Label display config.
    $build['settings']['label_display']['#default_value'] = FALSE;

    // Hidde block configuration if is new.
    $build['settings']['block_form']['#access'] = FALSE;

    // Add custom libraries.
    $build['#attached']['library'][] = 'layoutcomponents/layoutcomponents.lateral';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    $response = $this->rebuildAndClose($this->sectionStorage);
    $section = $this->sectionStorage->getSection($this->delta);
    $selector = $this->sectionStorage->getStorageId() . '/' . $this->delta . '/' . $section->getComponent($this->uuid)->getRegion() . '/' . $section->getComponent($this->uuid)->getUuid();
    $response->addCommand(new InvokeCommand('a[href*="' . $selector . '"].layout-builder__block_link-configure', 'click', []));

    return $response;
  }

}
