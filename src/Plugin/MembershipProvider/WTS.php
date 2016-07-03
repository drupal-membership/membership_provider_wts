<?php

namespace Drupal\membership_provider_wts\Plugin\MembershipProvider;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\membership_provider\Plugin\ConfigurableMembershipProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MembershipProvider (
 *   id = "wts",
 *   label = @Translation("WTS/ACHBill.com")
 * )
 */
class WTS extends ConfigurableMembershipProviderBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The signup base URL.
   */
  const SIGNUP_BASE = 'https://join.achbill.com/Signup/signup.cgi?chk:wts01:';

  /**
   * The reporting API URL.
   */
  const FTP = 'ftp.achbill.com';

  /**
   * Inactive membership state.
   */
  const STATUS_INACTIVE = 'expired';

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  private $dateFormatter;

  /**
   * Logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $loggerChannel;

  /**
   * @inheritDoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatter $dateFormatter, LoggerChannelInterface $loggerChannel) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $dateFormatter;
    $this->loggerChannel = $loggerChannel;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter'),
      $container->get('logger.channel.membership_provider_wts')
    );
  }


}
