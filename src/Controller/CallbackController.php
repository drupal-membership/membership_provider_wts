<?php

namespace Drupal\membership_provider_wts\Controller;

use Drupal\Core\Access\AccessException;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\membership_provider_wts\WTSEvent;
use Drupal\membership_provider_wts\WTSEvents;
use Drupal\membership_provider_wts\WTSResolveSiteEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\membership_provider\Plugin\MembershipProviderManager;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CallbackController.
 *
 * @package Drupal\membership_provider_wts\Controller
 */
class CallbackController extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private $currentRequest;

  /**
   * The site config.
   *
   * @var array
   */
  private $siteConfig;

  /**
   * Drupal\membership_provider\Plugin\MembershipProviderManager definition.
   *
   * @var \Drupal\membership_provider\Plugin\MembershipProviderManager
   */
  protected $plugin_manager_membership_provider_processor;

  /**
   * Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher definition.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $event_dispatcher;

  /**
   * Drupal\Core\Cache\DatabaseBackend definition.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The parsed request query string.
   *
   * @var array
   */
  protected $data;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack, MembershipProviderManager $plugin_manager_membership_provider_processor, ContainerAwareEventDispatcher $event_dispatcher, CacheBackendInterface $cache_default) {
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->plugin_manager_membership_provider_processor = $plugin_manager_membership_provider_processor;
    $this->event_dispatcher = $event_dispatcher;
    $this->cache = $cache_default;
    $this->data = \GuzzleHttp\Psr7\parse_query($this->currentRequest->getContent());
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('plugin.manager.membership_provider.processor'),
      $container->get('event_dispatcher'),
      $container->get('cache.default')
    );
  }

  /**
   * Set the site config, also checking access.
   *
   * @return array The site config.
   * @throws AccessException
   */
  private function setSiteConfig() {
    if ($cached = $this->cache->get('membership_provider_wts.site.' . $this->data['siteid'])) {
      $this->siteConfig = $cached->data;
    }
    else {
      $event = new WTSResolveSiteEvent($this->data['siteid']);
      $this->event_dispatcher->dispatch(WTSEvents::RESOLVE_SITE_CONFIG, $event);
      $this->siteConfig = $event->getSiteConfig();
      $this->cache->set('membership_provider_wts.site.' . $this->data['siteid'],
        $event->getSiteConfig(),
        Cache::PERMANENT,
        [$event->getSiteEntity()->getEntityType()->id() . ':' . $event->getSiteEntity()->id()]);
    }
    if ($this->getSiteConfig()['access_keyword'] != $this->data['sys_pass']) {
      throw new AccessException('Invalid sys_pass parameter.', 403);
    }
    return $this->getSiteConfig();
  }

  /**
   * POST callback handler.
   * 
   * @returns Response A text/plain response; the values are from the pseudo-code
   *   in the API documentation.
   */
  public function post() {
    $this->setSiteConfig();
    $event = new WTSEvent($this->getSiteConfig(), $this->data);
    switch ($this->data['action']) {
      case 'exists':
        $this->event_dispatcher->dispatch(WTSEvents::USERNAME_AVAILABLE, $event);
        $msg = $event->isFulfilled() ? '*failed*' : '*success*';
        break;
      case 'add':
        $this->event_dispatcher->dispatch(WTSEvents::APPEND, $event);
        $msg = $event->isFulfilled() ? '*success*' : '*error*';
        break;
      case 'delete':
        $this->event_dispatcher->dispatch(WTSEvents::DELETE, $event);
        $msg = $event->isFulfilled() ? '*success*' : '*error*';
        break;
      default:
        return $this->errorResponse('Invalid command.', 405);
    }
    $this->getLogger('membership_provider_wts')->debug($msg);
    return $this->blankResponse()->setContent($msg);
  }

  /**
   * @param $msg string Message
   * @param $code int Status code
   * @return \Symfony\Component\HttpFoundation\Response
   */
  private function errorResponse($msg, $code = 400) {
    return new Response($msg, $code, ['Content-Type' => 'text/plain']);
  }

  /**
   * @return \Symfony\Component\HttpFoundation\Response
   */
  private function blankResponse() {
    return new Response('', 200, ['Content-Type' => 'text/plain']);
  }

  /**
   * Access control callback.
   *
   * @return AccessResultInterface
   */
  public function access() {
    try {
      $this->setSiteConfig();
    }
    catch (\Throwable $e) {
      return AccessResult::forbidden();
    }
    return AccessResult::allowed();
  }

  /**
   * Get the current site config.
   *
   * @return array
   */
  public function getSiteConfig() {
    return $this->siteConfig;
  }

}
