<?php

namespace Drupal\membership_provider_wts\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'wts_memtype' widget.
 *
 * @FieldWidget(
 *   id = "wts_memtype",
 *   label = @Translation("WTS Memtype"),
 *   field_types = {
 *     "wts_memtype"
 *   }
 * )
 */
class WtsMemtypeWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element + array(
        '#type' => 'textfield',
        '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
        '#size' => $this->getSetting('size'),
        '#placeholder' => $this->t('ID'),
        '#maxlength' => $this->getFieldSetting('max_length'),
        '#attributes' => array('class' => array('js-text-full', 'text-full')),
        '#description' => $this->t('The SubID will be resolved by the plugin.')
      );

    return $element;
  }

}
