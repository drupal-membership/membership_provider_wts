<?php

namespace Drupal\membership_provider_wts\Plugin\MembershipProvider;

use Carbon\Carbon;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\membership_provider\Plugin\ConfigurableMembershipProviderBase;
use Masterminds\HTML5\Exception;
use phpseclib\Net\SFTP;
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

  const WTS_DATE_FORMAT = 'Ymd';

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

  /**
   * @inheritDoc
   */
  public function defaultConfiguration() {
    return [
      'site_id' => '',
      'account_id' => '',
      'access_keyword' => '',
      'sftp_password' => '',
    ];
  }

  /**
   * @inheritDoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = [
      'site_id' => $this->t('Site ID'),
      'account_id' => $this->t('Account ID'),
      'access_keyword' => $this->t('Access Keyword'),
      'sftp_password' => $this->t('SFTP Password'),
    ];
    $values = $this->getConfiguration() + $this->defaultConfiguration();
    foreach ($config as $key => $label) {
      $form[$key] = [
        '#type' => 'textfield',
        '#title' => $label,
        '#default_value' => $values[$key],
        '#size' => 60,
      ];
    }
    return $form;
  }

  public function formatFile(string $type, Carbon $date) {
    return "{$this->getConfiguration()['account_id']}-{$type}-WTS-"
      . $date->format(self::WTS_DATE_FORMAT)
      . ".txt";
  }

  public function fetchTransactions($since = NULL, string $type = 'trans') {
    $client = new SFTP(WTS::FTP);
    $config = $this->getConfiguration();
    $date = new Carbon($since, new \DateTimeZone('UTC'));
    $data = [];
    $transferred = 0;
    if ($client->login(strtolower($config['account_id']), $config['sftp_password'])) {
      $list = $client->rawlist('*' . $type . '*');
      while ($date->isPast()) {
        $file = $this->formatFile($type, $date);
        if (array_key_exists($file, $list)) {
          $recv = explode('\n', $client->get($file));
          if ($transferred) {
            // The first line is CSV headers, so keep only on first transfer.
            array_shift($recv);
          }
          array_merge($data, $recv);
          $transferred++;
        }
        else if ($date->diffInDays() > 2) {
          $exception = new Exception('Could not download ' . $file . 'despite valid date > 2 days ago.');
        }
        else {
          $exception = new Exception('Could not download ' . $file);
        }
        $date->addDay();
      }
      $client->disconnect();
    }
    else {
      // Create a proxy exception since SFTP doesn't throw them.
      $err = new Exception(
        'Unable to log in to WTS SFTP for account ' . $config['account_id'],
        0,
        new Exception($client->getLastSFTPError())
      );
      $this->loggerChannel->error($err->getMessage());
      throw $err;
    }
    // @todo - Parse CSV and return
    // @todo - Handle exception stored above.
  }

}
