<?php

namespace Drupal\membership_provider_wts\Tests;

use Drupal\simpletest\WebTestBase;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\membership_provider\Plugin\MembershipProviderManager;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Cache\DatabaseBackend;

/**
 * Provides automated tests for the membership_provider_wts module.
 */
class CallbackControllerTest extends WebTestBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request_stack;

  /**
   * Drupal\membership_provider\Plugin\MembershipProviderManager definition.
   *
   * @var Drupal\membership_provider\Plugin\MembershipProviderManager
   */
  protected $plugin_manager_membership_provider_processor;

  /**
   * Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher definition.
   *
   * @var Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $event_dispatcher;

  /**
   * Drupal\Core\Cache\DatabaseBackend definition.
   *
   * @var Drupal\Core\Cache\DatabaseBackend
   */
  protected $cache_default;
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "membership_provider_wts CallbackController's controller functionality",
      'description' => 'Test Unit for module membership_provider_wts and controller CallbackController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests membership_provider_wts functionality.
   */
  public function testCallbackController() {
    // Check that the basic functions of module membership_provider_wts.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
