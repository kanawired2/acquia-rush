<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Basic fields settings for LayoutComponents.
 */
class LcSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_settings_general';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'layoutcomponents.general',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('layoutcomponents.general');

    $form['general'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Provide the general configuration'),
      'config' => [
        '#type' => 'details',
        '#title' => $this->t('Block config'),
        '#group' => 'general',
        'folder' => [
          '#type' => 'textfield',
          '#title' => $this->t('Folder'),
          '#default_value' => $config->get('folder') ?? '',
          '#placeholder' => '/config/block_content',
          '#description' => $this->t('Set the folder where must be stored the blocks, the path will start with @root, the last slash will be added automatically', ['@root' => DRUPAL_ROOT]),
        ],
      ],
      'menu' => [
        '#type' => 'details',
        '#title' => $this->t('Lateral menu'),
        '#group' => 'general',
        'width' => [
          '#type' => 'number',
          '#title' => $this->t('Width'),
          '#min' => 200,
          '#max' => 1000,
          '#step' => 10,
          '#default_value' => $config->get('width') ?? 200,
          '#description' => $this->t('Select the width of the lateral menu'),
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $width = $form_state->getValue('width') ?: 200;
    $folder = $form_state->getValue('folder') ?: '';

    $this->config('layoutcomponents.general')
      ->set('folder', $folder)
      ->set('width', $width)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
