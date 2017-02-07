<?php

namespace Drupal\membership_provider_wts\Plugin\MembershipProvider;

use Carbon\Carbon;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\membership\Plugin\ConfigurableMembershipProviderBase;
use phpseclib\Net\SFTP;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\membership\Annotation\MembershipProvider;
use Drupal\Core\Annotation\Translation;

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
   * The PHP date() format string for the transaction record files.
   */
  const WTS_DATE_FORMAT = 'Ymd';

  /**
   * Transaction history file type - Transaction
   */
  const TYPE_TRANSACTIONS = 'trans';

  /**
   * Transaction history file type - Cancellations
   */
  const TYPE_CANCELLATIONS = 'cancel';

  /**
   * An auth code prefix indicating a test transaction.
   *
   * @var string
   */
  const TEST_PREFIX = 'Test Only:';

  /**
   * A transaction result code indicating an approved status.
   */
  const RESULT_APPROVED = 'Approved';

  /**
   * The timezone of the WTS server.
   *
   * @todo - Verify this.
   */
  const TIMEZONE = 'CST6CDT';

  /**
   * The name of the transaction key that can be referenced.
   */
  const TRANSACTION_KEY = 'History KeyID';

  /**
   * The name of the key that can reference TRANSACTION_KEY
   */
  const REFERENCE_KEY = 'Reference KeyID';

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

  /**
   * @inheritDoc
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement validateConfigurationForm() method.
  }

  /**
   * @param string $type
   * @param \Carbon\Carbon $date
   * @return string
   */
  public function formatFile(string $type, Carbon $date) {
    return "{$this->getConfiguration()['account_id']}-{$type}-WTS-"
      . $date->format(self::WTS_DATE_FORMAT)
      . ".txt";
  }

  /**
   * Fetch transactions from WTS SFTP.
   *
   * @param string $since Date parseable by \DateTime to fetch from, inclusive.
   *   Will be interpreted in UTC.
   * @param bool $includeTest Whether to include test transactions.
   * @param string $type Type of transaction files to query.
   * @throws \Exception
   * @return array Array:
   *   - Array of transactions
   *   - Carbon object for the last date successfully transferred.
   */
  public function fetchTransactions(string $since, $includeTest = false, string $type = self::TYPE_TRANSACTIONS) {
    $client = new SFTP(self::FTP);
    $config = $this->getConfiguration();
    $date = new Carbon($since, new \DateTimeZone(self::TIMEZONE));
    $data = [];
    $transferred = false;
    // Assumption: Login is the lowercase equivalent to Account ID.
    if ($client->login(strtolower($config['account_id']), $config['sftp_password'])) {
      $list = $client->rawlist();
      while ($date->isPast()) {
        $file = $this->formatFile($type, $date);
        if (array_key_exists($file, $list) && ($content = $client->get($file))) {
          // @see http://stackoverflow.com/a/29471912/4447064
          $recv = array_filter(preg_split("/\\r\\n|\\r|\\n/", $content));
          if (!empty($transferred)) {
            // The first line is CSV headers, so keep only on first transfer.
            array_shift($recv);
          }
          $data = array_merge($data, $recv);
          $transferred = clone $date;
        }
        else if ($date->diffInDays() > 2) {
          throw new \Exception('Could not download ' . $file . ' despite valid date > 2 days ago.');
        }
        $date->addDay();
      }
      $client->disconnect();
      if (!$transferred) {
        throw new \Exception('No transaction files received querying from ' . $since);
      }
    }
    else {
      // Create a proxy exception since SFTP doesn't throw them.
      $err = new \Exception(
        'Unable to log in to WTS SFTP for account ' . $config['account_id'],
        0,
        new \Exception($client->getLastSFTPError())
      );
      $this->loggerChannel->error($err->getMessage());
      throw $err;
    }
    return [
      $this->parseTransactions($data, $includeTest),
      $transferred,
    ];
  }

  /**
   * Parse the data file response into an associative array.
   *
   * @param array $data CSV data with first array item containing keys.
   * @param boolean $includeTest Flag indicating whether to include test transactions.
   * @return array Array of transactions
   */
  protected function parseTransactions(array $data, $includeTest = false) {
    $trans = [];
    $keys = [];
    $csv = array_map('str_getcsv', $data);
    foreach ($csv as $row => $line) {
      if ($row === 0) {
        $keys = $line;
      }
      else {
        $member = array_combine($keys, $line);
        if (!$includeTest && strpos($member['Authorization Code'], self::TEST_PREFIX) === 0) {
          continue;
        }
        $trans[] = $member;
      }
    }
    return $trans;
  }

  /**
   * @inheritDoc
   */
  public function configureFromId($id) {
    if ($config = $this->resolver->getSiteConfigById($id)) {
      $this->setConfiguration($config);
      return $this;
    }
    return FALSE;
  }

}
