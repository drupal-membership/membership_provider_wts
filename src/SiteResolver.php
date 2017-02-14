<?php

namespace Drupal\membership_provider_wts;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Class SiteResolver.
 *
 * @package Drupal\membership_provider_wts
 */
class SiteResolver {

  /**
   * Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher definition.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $event_dispatcher;

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Constructor.
   */
  public function __construct(ContainerAwareEventDispatcher $event_dispatcher, CacheBackendInterface $cache) {
    $this->event_dispatcher = $event_dispatcher;
    $this->cache = $cache;
  }

  /**
   * Get a site config for a given site ID.
   *
   * @param $site_tag
   * @return array|null
   */
  public function getSiteConfig($site_tag) {
    $key = 'membership_provider_wts.site.' . $site_tag;
    if ($cached = $this->cache->get($key)) {
      $siteConfig = $cached->data;
    }
    else {
      $event = new WTSResolveSiteEvent($site_tag);
      $this->event_dispatcher->dispatch(WTSEvents::RESOLVE_SITE_CONFIG, $event);
      try {
        $siteConfig = $event->getSiteConfig();
      }
      catch (\Exception $e) {
        return NULL;
      }
      $this->cache->set($key,
        $event->getSiteConfig(),
        Cache::PERMANENT,
        [$event->getSiteEntity()->getEntityType()->id() . ':' . $event->getSiteEntity()->id()]);
    }
    return $siteConfig;
  }

}
