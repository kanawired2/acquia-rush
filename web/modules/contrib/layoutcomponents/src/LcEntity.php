<?php

namespace Drupal\layoutcomponents;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\layoutcomponents\Entity\LcEntityViewDisplay;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\block_content\Entity\BlockContent;
use Drupal\field\Entity\FieldConfig;
use Drupal\layoutcomponents\Api\Component;

/**
 * General class for Entity hooks.
 */
class LcEntity implements ContainerInjectionInterface{

  /**
   * The LcApi object.
   *
   * @var \Drupal\layoutcomponents\Api\Component
   */
  protected $lcApi;

  /**
   * {@inheritdoc}
   */
  public function __construct(Component $lc_api) {
    $this->lcApi = $lc_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layoutcomponents.apiComponent')
    );
  }

  /**
   * Implements hook_page_attachments_alter() for LC pages.
   *
   * @see \hook_entity_type_alter()
   */
  public function entityTypeAlter(array &$entity_types) {
    if (isset($entity_types['entity_view_display'])) {
      $entity_types['entity_view_display']->setClass(LcEntityViewDisplay::class);
    }
  }

  /**
   * Implements hook_page_attachments_alter() for LC pages.
   *
   * @see \hook_inline_entity_form_entity_form_alter()
   */
  public function inlineEntityFormEntityFormAlter(&$entity_form, &$form_state) {
    if (!array_key_exists('#default_value', $entity_form) || !isset($entity_form['#default_value'])) {
      return;
    }
    if ($entity_form['#default_value'] instanceof BlockContent) {
      $this->layoutcomponentsFormAlter($entity_form, $entity_form['#default_value']);
    }
  }

  /**
   * Implements hook_block_type_form_alter() for LC pages.
   *
   * @see \hook_block_type_form_alter()
   */
  public function blockTypeFormAlter(array &$form, FormStateInterface &$form_state, $block_type) {
    if (!array_key_exists('#block', $form)) {
      return;
    }
    $this->layoutcomponentsFormAlter($form, $form['#block']);
  }

  /**
   * Change the elements with LayoutComponents Api.
   *
   * @param array $form
   *   The array with the form.
   */
  public function layoutcomponentsFormAlter(array &$form, BlockContent $element) {
    /** @var \Drupal\block_content\Entity\BlockContent $block */
    $block = $element;

    $definitions = $block->getFieldDefinitions();
    foreach ($definitions as $definition) {
      if ($definition instanceof FieldConfig) {
        if (array_key_exists($definition->getName(), $form)) {
          $item = [];
          if (array_key_exists('0', $form[$definition->getName()]['widget'])) {
            if (!empty($form[$definition->getName()]['widget'][0]['value'])) {
              $item = $form[$definition->getName()]['widget'][0]['value'];
              $form[$definition->getName()]['widget'][0]['value'] = $this->lcApi->getComponentElement(
                [
                  'no_lc' => TRUE,
                ],
                $item
              );
            }
            else {
              $item = $form[$definition->getName()]['widget'][0];
              $form[$definition->getName()]['widget'][0] = $this->lcApi->getComponentElement(
                [
                  'no_lc' => TRUE,
                ],
                $item
              );
            }
          }
          else {
            $item = $form[$definition->getName()]['widget'];
            $form[$definition->getName()]['widget'] = $this->lcApi->getComponentElement(
              [
                'no_lc' => TRUE,
              ],
              $item
            );
          }
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_block() for LC pages.
   *
   * @see \hook_preprocess_block()
   */
  public function preprocessBlock(array &$variables) {
    // Filter blocks with layout_builder.
    if (array_key_exists('title_suffix', $variables)) {
      if (array_key_exists('contextual_links', $variables['title_suffix'])) {
        $id = $variables['title_suffix']['contextual_links']['#id'] ?: NULL;
        if (!empty($id)) {
          if (strpos($id, "layout_builder_block:") !== FALSE) {
            // Insert configuration block.
            $variables['title_suffix']['layout_builder-configuration'] = $variables['content']['layout_builder-configuration'];

            // Remove contextual links to all layout builder elements.
            unset($variables['title_suffix']['contextual_links']);
            unset($variables['content']['layout_builder-configuration']);
          }
        }
      }
    }

    // Provide default class for system_main_block.
    if ($variables['plugin_id'] == 'system_main_block') {
      $variables['attributes']['class'][] = 'lc-main-content';
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_view_alter() for LC pages.
   *
   * @see \hook_ENTITY_TYPE_view_alter()
   */
  public function blockContentViewAlter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display) {
    if (isset($build['_layout_builder']) || isset($entity->view) || isset($entity->_referringItem)) {
      $build['#theme'] = 'layoutcomponents_block_content';
    }
  }

}
