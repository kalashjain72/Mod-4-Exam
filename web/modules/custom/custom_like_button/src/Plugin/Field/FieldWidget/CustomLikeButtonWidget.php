<?php

namespace Drupal\custom_like_button\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'custom_like_button_widget' widget.
 *
 * @FieldWidget(
 *   id = "custom_like_button_widget",
 *   label = @Translation("Custom Like Button"),
 *   field_types = {
 *     "custom_like_button"
 *   }
 * )
 */
class CustomLikeButtonWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : 0;

    $element['value'] = [
      '#type' => 'button',
      '#value' => t('Like'),
      '#name' => $items->getName() . "[$delta]",
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => 'custom-like-button-wrapper-' . $items->getName() . '-' . $delta,
      ],
    ];

    $element['#suffix'] = '<div id="custom-like-button-wrapper-' . $items->getName() . '-' . $delta . '">' . $value . '</div>';

    return $element;
  }

  /**
   * Ajax callback for the like button.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $element_key = $triggering_element['#array_parents'][0];
    $delta = $triggering_element['#array_parents'][1];

    $items = $form_state->getValue($element_key);
    $items[$delta]['value']++;
    $form[$element_key][$delta]['#markup'] = $items[$delta]['value'];

    return $form[$element_key][$delta];
  }

}
