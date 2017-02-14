<?php

namespace Drupal\membership_provider_wts\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'wts_link' formatter.
 *
 * @FieldFormatter(
 *   id = "wts_link",
 *   label = @Translation("WTS Signup Link"),
 *   field_types = {
 *     "wts_memtype"
 *   }
 * )
 */
class WtsLinkFormatter extends FormatterBase {

  /**
   * The signup URL.
   *
   * @var string
   */
  const SIGNUP_URL = 'https://join.achbill.com/Signup/signup.cgi';

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      // Implement default settings.
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array(
      // Implement settings form.
    ) + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    // Implement settings summary.

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $siteInfo = "?chk:wts01:{$item->subid}:";
    // Need special treatment for this query string due to URL encoding.
    $query['memtype'] = $item->value;
    $url = Url::fromUri(self::SIGNUP_URL . $siteInfo, ['query' => $query]);
    return Link::fromTextAndUrl($this->t('Join via ACH (e-check)'), $url)->toString();
  }

}
