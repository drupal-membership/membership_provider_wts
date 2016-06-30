<?php

namespace Drupal\membership_provider_wts;

/**
 * Class WTSEvents
 *
 * Contains events called by the WTS membership provider.
 *
 * @package Drupal\membership_provider_wts
 */
final class WTSEvents {

  /**
   * Name of the append event.
   */
  const APPEND = 'membership_provider_wts.append';

  /**
   * Name of the append event.
   */
  const DELETE = 'membership_provider_wts.delete';

  /**
   * Name of the user check event.
   */
  const USERNAME_AVAILABLE = 'membership_provider_wts.checkuser';

  /**
   * Config resolution
   * 
   * @Event
   */
  const RESOLVE_SITE_CONFIG = 'membership_provider_wts.resolver';

}
