<?php

namespace Drupal\membership_provider_wts\Plugin\MembershipProvider;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\membership_provider\Plugin\MembershipProviderBase;
use Drupal\membership_provider\Plugin\MembershipProviderInterface;
use Drupal\membership_provider_netbilling\NetbillingUtilities;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @MembershipProvider (
 *   id = "wts",
 *   label = @Translation("WTS/ACHBill.com")
 * )
 */
class WTS extends MembershipProviderBase implements MembershipProviderInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

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
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  private $loggerChannelFactory;

  /**
   * @inheritDoc
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatter $dateFormatter, LoggerChannelFactory $loggerChannelFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $dateFormatter;
    $this->loggerChannelFactory = $loggerChannelFactory;
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
      $container->get('logger.factory')
    );
  }

}
