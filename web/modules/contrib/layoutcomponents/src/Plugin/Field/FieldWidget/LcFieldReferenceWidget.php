<?php

namespace Drupal\layoutcomponents\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\ContentEntityType;

/**
 * Plugin of Layoutcomponents Field type.
 *
 * @FieldWidget(
 *   id = "layoutcomponents_entity_reference",
 *   module= "layoutcomponents",
 *   label = @Translation("Layoutcomponents field reference"),
 *   description = @Translation("An select list of fields of current node."),
 *   field_types = {
 *     "layoutcomponents_field_reference",
 *   }
 * )
 */
class LcFieldReferenceWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = [];

    $type_options = $this->getEntityTypes();
    $element['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Entity'),
      '#options' => $type_options,
      '#default_value' => isset($items[$delta]->entity_type) ? $items[$delta]->entity_type : 'none',
      '#ajax' => [
        'callback' => [$this, 'ajaxEntityContent'],
        'event' => 'change',
        'wrapper' => 'entity-id',
      ],
      '#prefix' => '<span id="entity_type">',
      '#suffix' => '</span>',
      '#required' => TRUE,
    ];

    $element['entity_id_context'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Get ID by current URL'),
      '#default_value' => isset($items[$delta]->entity_id_context) ? $items[$delta]->entity_id_context : 0,
      '#ajax' => [
        'callback' => [$this, 'ajaxEntityFields'],
        'event' => 'change',
        'wrapper' => 'entity-id-context',
      ],
      '#prefix' => '<span id="entity_id_context">',
      '#suffix' => '</span>',
    ];

    $content_options = [];
    if (isset($items[$delta]->entity_type) && !empty($items[$delta]->entity_type)) {
      $content_options = $this->getEntityContent($items[$delta]->entity_type);
    }

    $element['entity_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Select entity ID'),
      '#options' => $content_options,
      '#default_value' => isset($items[$delta]->entity_id) ? $items[$delta]->entity_id : 'none',
      '#ajax' => [
        'callback' => [$this, 'ajaxEntityFields'],
        'event' => 'change',
        'wrapper' => 'entity-id',
      ],
      '#prefix' => '<span id="entity_id">',
      '#suffix' => '</span>',
      '#validated' => TRUE,
      '#states' => [
        'invisible' => [
          ':input[name="settings[block_form][field_reference_field][0][entity_id_context]"]' => ['checked' => TRUE],
        ],
        'visible' => [
          ':input[name="settings[block_form][field_reference_field][0][entity_id_context]"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $field_options = [];
    if (isset($items[$delta]->entity_id_context) && !empty($items[$delta]->entity_id_context)) {
      $field_options = $this->getEntityFieldsByEntityType($items[$delta]->entity_type);
    }
    elseif (isset($items[$delta]->entity_id) && !empty($items[$delta]->entity_id)) {
      $field_options = $this->getEntityFieldsByBundle($items[$delta]->entity_type, $items[$delta]->entity_id);
    }

    $element['entity_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Select field'),
      '#options' => $field_options,
      '#default_value' => isset($items[$delta]->entity_field) ? $items[$delta]->entity_field : 'none',
      '#description' => t('Select the field of current entity to render'),
      '#prefix' => '<span id="entity_field">',
      '#suffix' => '</span>',
      '#validated' => TRUE,
      '#required' => TRUE,
    ];

    $element['entity_field_label'] = [
      '#type' => 'select',
      '#title' => $this->t('Select field label'),
      '#required' => TRUE,
      '#options' => [
        'above' => 'above',
        'inline' => 'inline',
        'hidden' => 'hidden',
        'visually_hidden' => 'visually hidden',
      ],
      '#default_value' => isset($items[$delta]->entity_field_label) ? $items[$delta]->entity_field_label : 'above',
    ];

    return $element;
  }

  /**
   * Provide the tntity types processed.
   */
  public function getEntityTypes() {
    $inital_array = ['none' => 'None'];
    $types = \Drupal::entityTypeManager()->getDefinitions();
    foreach ($types as $name => $type) {
      if ($type instanceof ContentEntityType) {
        $res[$type->id()] = $type->getLabel()->render();
      }
    }
    ksort($res);
    $res = array_merge($inital_array, $res);
    return $res;
  }

  /**
   * Provide the tntity types processed.
   *
   * @param string $type
   *   The content type.
   */
  public function getEntityContent($type = '') {
    $inital_array = ['none' => 'None'];
    if (!empty($type) && $type !== 'none') {
      // All content of current type.
      $content = \Drupal::entityTypeManager()->getStorage($type)->loadMultiple();
      if (!empty($content)) {
        foreach ($content as $key => $item) {
          $res[$item->id() . '-' . $item->get('type')->getString()] = $item->label();
        }
      }
    }
    ksort($res);
    $res = array_merge($inital_array, $res);
    return $res;
  }

  /**
   * Provide the tntity types processed.
   *
   * @param string $entity_type
   *   The entity type.
   */
  public function getEntityFieldsByEntityType($entity_type) {
    $inital_array = ['none' => 'None'];
    if (!empty($entity_type)) {
      $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type);
      if (!empty($bundles)) {
        $bundles = array_keys($bundles);
        foreach ($bundles as $key => $bundle) {
          $entity_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
          if (!empty($entity_fields)) {
            foreach ($entity_fields as $key => $field) {
              $res[$key] = $key;
            }
          }
        }
      }
    }
    ksort($res);
    $res = array_merge($inital_array, $res);
    return $res;
  }

  /**
   * Provide the tntity types processed.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $entity
   *   The entity.
   */
  public function getEntityFieldsByBundle($entity_type, $entity) {
    $inital_array = ['none' => 'None'];
    if (!empty($entity_type) && !empty($entity) && $entity !== 'none') {
      $bundle = explode('-', $entity);
      if (count($bundle) > 1) {
        $bundle = isset($bundle[1]) ? $bundle[1] : NULL;
        if (!empty($bundle)) {
          $entity_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
          if (!empty($entity_fields)) {
            foreach ($entity_fields as $key => $field) {
              $res[$key] = $key;
            }
          }
        }
      }
    }
    ksort($res);
    $res = array_merge($inital_array, $res);
    return $res;
  }

  /**
   * Provide the tntity types processed.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface object.
   */
  public function ajaxEntityContent(array &$form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    // Get trigger element.
    $element = $form_state->getTriggeringElement();
    // Get subform elements.
    $entity_type = NestedArray::getValue($form_state->getValues(), $element['#parents']);
    $parents = array_slice($element['#array_parents'], 0, -1);
    $entity_id = NestedArray::getValue($form, $parents)['entity_id'];
    if (!empty($entity_type) && !empty($entity_id)) {
      // Ste new options.
      $entity_id['#options'] = $this->getEntityContent($entity_type);
      $response->addCommand(new ReplaceCommand('#entity_id', $entity_id));
    }
    return $response;
  }

  /**
   * Provide the tntity types processed.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface object.
   */
  public function ajaxEntityFields(array &$form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    // Get trigger element.
    $element = $form_state->getTriggeringElement();
    // Get subform elements.
    $parents = array_slice($element['#array_parents'], 0, -1);
    $entity_field = NestedArray::getValue($form, $parents)['entity_field'];
    unset($parents[array_search('widget', $parents)]);
    $data = NestedArray::getValue($form_state->getValues(), $parents);
    $entity_id_context = isset($data['entity_id_context']) ? $data['entity_id_context'] : NULL;
    $entity_type = isset($data['entity_type']) ? $data['entity_type'] : NULL;
    // If we get the ID by the current URL.
    if (empty($entity_id_context)) {
      $entity = isset($data['entity_id']) ? $data['entity_id'] : NULL;
      if (!empty($entity_type) && !empty($entity)) {
        // Set new options.
        $entity_field['#options'] = $this->getEntityFieldsByBundle($entity_type, $entity);
      }
    }
    else {
      $entity_field['#options'] = $this->getEntityFieldsByEntityType($entity_type);
    }
    $response->addCommand(new ReplaceCommand('#entity_field', $entity_field));
    return $response;
  }

}
