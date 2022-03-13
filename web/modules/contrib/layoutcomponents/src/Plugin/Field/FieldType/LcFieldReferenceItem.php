<?php

namespace Drupal\layoutcomponents\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Field\FieldItemBase;

/**
 * Defines the 'Layoutcomponents field reference' entity field type.
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 *
 * @FieldType(
 *   id = "layoutcomponents_field_reference",
 *   label = @Translation("Layoutcomponents field reference"),
 *   description = @Translation("A field reference to current entity."),
 *   category = @Translation("Reference"),
 *   default_widget = "layoutcomponents_entity_reference",
 *   default_formatter = "layoutcomponents_entity_formatter",
 * )
 */
class LcFieldReferenceItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'plugin_types' => ['block' => 'block'],
      'preselect_nodes' => [],
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['entity_id_context'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity id context'))
      ->setDescription(new TranslatableMarkup('The referenced entity id context'));

    $properties['entity_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity Type'))
      ->setDescription(new TranslatableMarkup('The referenced entity type'));

    $properties['entity_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity Id'))
      ->setDescription(new TranslatableMarkup('The referenced entity id'));

    $properties['entity_field'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity Field'))
      ->setDescription(new TranslatableMarkup('The referenced entity field'));

    $properties['entity_field_label'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Entity Field Label'))
      ->setDescription(new TranslatableMarkup('The referenced entity field label'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'entity_id_context' => [
          'type' => 'blob',
          'size' => 'big',
          'not null' => TRUE,
          'serialize' => TRUE,
        ],
        'entity_type' => [
          'description' => 'The type of the entity.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => TRUE,
          'serialize' => TRUE,
        ],
        'entity_id' => [
          'description' => 'The ID of the entity.',
          'type' => 'blob',
          'size' => 'big',
          'not null' => TRUE,
          'serialize' => TRUE,
        ],
        'entity_field' => [
          'type' => 'blob',
          'size' => 'big',
          'not null' => TRUE,
          'serialize' => TRUE,
        ],
        'entity_field_label' => [
          'type' => 'blob',
          'size' => 'big',
          'not null' => TRUE,
          'serialize' => TRUE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $isEmpty =
      empty($this->get('entity_id_context')->getValue()) &&
      empty($this->get('entity_type')->getValue()) &&
      empty($this->get('entity_id')->getValue()) &&
      empty($this->get('entity_field')->getValue()) &&
      empty($this->get('entity_field_label')->getValue());

    return $isEmpty;
  }

}
