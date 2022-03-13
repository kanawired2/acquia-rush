<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Basic fields settings for LayoutComponents.
 */
class LcColorsSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_settings_colors';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'layoutcomponents.colors',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('layoutcomponents.colors');

    $form['general'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Provide the default configuration of the colors'),
      'editor_colors' => [
        '#type' => 'details',
        '#title' => $this->t('Editor Colors'),
        '#group' => 'general',
        'editor_colors_list' => [
          '#type' => 'textarea',
          '#title' => $this->t('Colors'),
          '#rows' => 5,
          '#cols' => 5,
          '#default_value' => $config->get('editor_colors_list') ?? '',
          '#description' => $this->t('Add the list of colors that you want to use in LC editor for sections and columns, ej: #ffffff,#f89456'),
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $colors = $form_state->getValue('editor_colors_list') ?: '#000000';

    $this->config('layoutcomponents.colors')
      ->set('editor_colors_list', $colors)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
