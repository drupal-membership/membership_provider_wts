<?php

namespace Drupal\membership_provider_wts;

use Symfony\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;

/**
 * Event class to complete the site config.
 *
 * @package Drupal\membership_provider_netbilling
 */
class WTSResolveSiteEvent extends Event {

  /**
   * The site config.
   *
   * @var array
   */
  private $siteConfig;

  /**
   * The site entity.
   *
   * @var EntityInterface
   */
  private $siteEntity;

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
