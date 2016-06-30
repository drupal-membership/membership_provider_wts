<?php

namespace Drupal\membership_provider_wts\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'wts_site_config' widget.
 *
 * @FieldWidget(
 *   id = "wts_site_config",
 *   label = @Translation("WTS Site Config"),
 *   field_types = {
 *     "wts_site_config"
 *   }
 * )
 */
class WTSSiteConfig extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#type'] = 'fieldset';
    $properties = $this->fieldDefinition->getFieldStorageDefinition()->getPropertyDefinitions();
    // This loop is only appropriate since we know the fields all have the same type/config.
    foreach ($properties as $key => $item) {
      $element[$key] = [
        '#type' => 'textfield',
        '#title' => $item->getLabel(),
        '#default_value' => isset($items[$delta]->{$key}) ? $items[$delta]->{$key} : NULL,
        '#size' => $this->getSetting('size'),
      ];
    }
    return $element;
  }

}
