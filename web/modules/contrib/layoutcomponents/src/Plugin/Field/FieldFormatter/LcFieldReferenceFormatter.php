<?php

namespace Drupal\layoutcomponents\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Field formatter for Viewsreference Field.
 *
 * @FieldFormatter(
 *   id = "layoutcomponents_entity_formatter",
 *   label = @Translation("Default"),
 *   field_types = {"layoutcomponents_field_reference"}
 * )
 */
class LcFieldReferenceFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $options = parent::defaultSettings();
    $options['plugin_types'] = ['block'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];

    $view_mode = 'default';

    foreach ($items as $delta => $item) {
      $entity_type = $items->getValue()[$delta]['entity_type'];
      $entity = $items->getValue()[$delta]['entity_id'];
      $entity_id_context = $items->getValue()[$delta]['entity_id_context'];
      $entity_field = $items->getValue()[$delta]['entity_field'];
      if (!empty($entity_type) && !empty($entity_field)) {
        // If entity id takes from current URL or with direct ID.
        if (!empty($entity_id_context)) {
          $path = parse_url(\Drupal::requestStack()->getCurrentRequest()->getRequestUri(), PHP_URL_PATH);
          if (!empty($path)) {
            $source_uri = \Drupal::service('path_alias.manager')->getPathByAlias($path);
            if (!empty($source_uri)) {
              $params = Url::fromUri("internal:" . $source_uri)->getRouteParameters();
              if (!empty($params)) {
                $current_entity_type = key($params);
                if (!empty($current_entity_type)) {
                  // Check if the current node is equal as the entity type.
                  if ($current_entity_type == $entity_type) {
                    $entity_id = isset($params[$entity_type]) ? $params[$entity_type] : NULL;
                    if (!empty($entity_id)) {
                      $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($params[$entity_type]);
                      if (!empty($entity)) {
                        $bundle = $entity->bundle();
                      }
                    }
                  }
                }
              }
            }
          }
        }
        else {
          if (!empty($entity)) {
            $entity = explode('-', $entity);
            if (count($entity) > 1) {
              $entity_id = isset($entity[0]) ? $entity[0] : NULL;
              $bundle = isset($entity[1]) ? $entity[1] : NULL;
              if (!empty($entity_id) && !empty($bundle)) {
                $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id);
              }
            }
          }
        }
        // Once that we have loaded the bundle and entity we build the display.
        if (!empty($entity) && !empty($bundle)) {
          // Load field.
          $field = $entity->get($entity_field);
          if (!empty($field)) {
            // Set the display of the field.
            $display = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load($entity_type . '.' . $bundle . '.' . $view_mode);
            if (!empty($display)) {
              // Set display settings of the field.
              $display_options = $display->getComponent($entity_field);
              $display_options['label'] = $items->getValue()[$delta]['entity_field_label'];
              // Build the field.
              $field_view = $field->view($display_options);
              // Render the field.
              $elements[$delta] = [
                '#type' => 'markup',
                '#markup' => \Drupal::service('renderer')->render($field_view),
              ];
            }
          }
        }
      }
    }

    return $elements;
  }

}
