<?php

namespace Drupal\layoutcomponents\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Basic fields settings for LayoutComponents.
 */
class LcInterfaceSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layoutcomponents_settings_interface';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'layoutcomponents.interface',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config $config */
    $config = $this->config('layoutcomponents.interface');

    $form['general'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Provide the default configuration of the interface'),
      'theme' => [
        '#type' => 'details',
        '#title' => $this->t('Theme'),
        '#group' => 'general',
        'theme_type' => [
          '#type' => 'select',
          '#title' => $this->t('Theme Type'),
          '#options' => [
            'color-dark' => $this->t('Color Dark'),
            'grey-dark' => $this->t('Color Grey Dark'),
          ],
          '#default_value' => $config->get('theme_type') ?? 'color-dark',
          '#description' => $this->t('Select the theme for LC'),
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $theme = $form_state->getValue('theme_type') ?: 'color-dark';

    $this->config('layoutcomponents.interface')
      ->set('theme_type', $theme)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
