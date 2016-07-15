<?php

namespace Drupal\membership_provider_wts;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class NetbillingEvent
 *
 * @package Drupal\membership_provider_netbilling
 */
class WTSEvent extends Event {

  /**
   * Status indicating the original request has been fulfilled.
   */
  const STATUS_FULFILLED = 1;

  /**
   * The site config.
   *
   * @var array
   */
  private $siteConfig;

  /**
   * The query data.
   *
   * @var array
   */
  private $data = [];

  /**
   * Processing status.
   * 
   * @var int
   */
  private $status;

  /**
   * Status message.
   * 
   * @var string
   */
  private $message = '';

  /**
   * Set the status message.
   * 
   * @param string $message
   */
  public function setMessage($message) {
    $this->message = $message;
  }

  public function __construct($config, $data) {
    $this->data = $data;
    $this->siteConfig = $config;
  }

  /**
   * Report whether the request has been yet fulfilled.
   * 
   * @return bool
   */
  public function isFulfilled() {
    return $this->status == self::STATUS_FULFILLED;
  }

  /**
   * Set the fulfillment status.
   * 
   * @param $status
   */
  public function setStatus($status) {
    $this->status = $status;
  }

  /**
   * Get the site config.
   * 
   * @return array
   */
  public function getSiteConfig() {
    return $this->siteConfig;
  }

  /**
   * Get the arbitrary data.
   * 
   * @return array
   */
  public function getData() {
    return $this->data;
  }

  public function getUser() {
    return $this->getData()['username'];
  }

}
