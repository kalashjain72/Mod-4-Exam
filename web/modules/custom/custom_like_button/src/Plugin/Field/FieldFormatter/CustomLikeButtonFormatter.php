<?php

namespace Drupal\custom_like_button\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'custom_like_button_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "custom_like_button_formatter",
 *   label = @Translation("Custom Like Button"),
 *   field_types = {
 *     "custom_like_button"
 *   }
 * )
 */
class CustomLikeButtonFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => $this->t('Likes: @count', ['@count' => $item->value]),
      ];
    }

    return $elements;
  }

}
