<?php

namespace Drupal\custom_like_button\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'custom_like_button' field type.
 *
 * @FieldType(
 *   id = "custom_like_button",
 *   label = @Translation("Custom Like Button"),
 *   description = @Translation("A custom like button field."),
 *   category = @Translation("General"),
 *   default_widget = "custom_like_button_widget",
 *   default_formatter = "custom_like_button_formatter"
 * )
 */
class CustomLikeButton extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];

    $properties['value'] = DataDefinition::create('integer')
      ->setLabel(t('Like count'))
      ->setDescription(t('The number of likes.'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'value' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return empty($this->get('value')->getValue());
  }

}
