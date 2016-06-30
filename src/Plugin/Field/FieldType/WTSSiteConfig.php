<?php

namespace Drupal\membership_provider_wts\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'wts_site_config' field type.
 *
 * @FieldType(
 *   id = "wts_site_config",
 *   label = @Translation("WTS Site Config"),
 *   description = @Translation("WTS Site Config"),
 *   default_widget = "wts_site_config",
 *   default_formatter = "wts_site_config"
 * )
 */
class WTSSiteConfig extends FieldItemBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return array(
      'max_length' => 255,
      'is_ascii' => TRUE,
      'case_sensitive' => TRUE,
    ) + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $max_length = $field_definition->getSetting('max_length');
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['account_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Account ID'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->addConstraint('Length', array('max' => $max_length))
      ->setRequired(TRUE);
    $properties['sub_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Site Tag'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->addConstraint('Length', array('max' => $max_length))
      ->addConstraint('WTSUniqueSite')
      ->setRequired(TRUE);
    $properties['access_keyword'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Access Keyword'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->addConstraint('Length', array('max' => $max_length))
      ->setRequired(TRUE);
    $properties['sftp_password'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('SFTP Download Password'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->addConstraint('Length', array('max' => $max_length))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * @inheritDoc
   */
  public function setValue($values, $notify = TRUE) {
    // @see https://www.drupal.org/node/2349819
    foreach ($values as $k => $v) {
      if ($v == '') {
        $values[$k] = NULL;
      }
    }
    parent::setValue($values, $notify);
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = array(
      'columns' => array(
        'account_id' => array(
          'type' => $field_definition->getSetting('is_ascii') === TRUE ? 'varchar_ascii' : 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
          'binary' => $field_definition->getSetting('case_sensitive'),
          'not_null' => TRUE,
        ),
        'sub_id' => array(
          'type' => $field_definition->getSetting('is_ascii') === TRUE ? 'varchar_ascii' : 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
          'binary' => $field_definition->getSetting('case_sensitive'),
          'not_null' => TRUE,
        ),
        'access_keyword' => array(
          'type' => $field_definition->getSetting('is_ascii') === TRUE ? 'varchar_ascii' : 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
          'binary' => $field_definition->getSetting('case_sensitive'),
          'not_null' => TRUE,
        ),
        'sftp_password' => array(
          'type' => $field_definition->getSetting('is_ascii') === TRUE ? 'varchar_ascii' : 'varchar',
          'length' => (int) $field_definition->getSetting('max_length'),
          'binary' => $field_definition->getSetting('case_sensitive'),
          'not_null' => TRUE,
        ),
      ),
    );

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['sub_id'] = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));
    $values['account_id'] = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));
    $values['access_keyword'] = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));
    $values['sftp_password'] = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $account_id = $this->get('account_id')->getValue();
    $sub_id = $this->get('sub_id')->getValue();
    $access_keyword = $this->get('access_keyword')->getValue();
    $sftp_password = $this->get('sftp_password')->getValue();
    return empty($account_id) && empty($sub_id) && empty($access_keyword) && empty($sftp_password);
  }

}
