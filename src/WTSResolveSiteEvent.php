<?php

namespace Drupal\membership_provider_wts;

use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;

/**
 * Event class to complete the site config.
 *
 * @package Drupal\membership_provider_wts
 */
class WTSResolveSiteEvent extends Event {

  /**
   * The site config.
   *
   * @var array
   */
  protected $siteConfig;

  /**
   * The site entity.
   *
   * @var EntityInterface
   */
  protected $siteEntity;

  /**
   * The remote ID
   *
   * @var string
   */
  protected $remoteId;

  /**
   * @inheritDoc
   */
  public function __construct($sub_id) {
    $this->siteConfig = ['sub_id' => $sub_id];
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
   * @return mixed
   */
  public function getRemoteId() {
    return $this->remoteId;
  }

  /**
   * @param mixed $remoteId
   */
  public function setRemoteId($remoteId) {
    $this->remoteId = $remoteId;
  }

  /**
   * Set the site config.
   *
   * @param $config
   */
  public function setSiteConfig($config) {
    $this->siteConfig = $config;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getSiteEntity() {
    return $this->siteEntity;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $siteEntity
   */
  public function setSiteEntity($siteEntity) {
    $this->siteEntity = $siteEntity;
  }

}
