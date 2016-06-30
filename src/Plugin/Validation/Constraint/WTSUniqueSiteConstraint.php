<?php

namespace Drupal\membership_provider_wts\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Enforces unique Site IDs.
 *
 * @Constraint(
 *   id = "WTSUniqueSite",
 *   label = @Translation("Unique Site ID", context = "Validation")
 * )
 */
class WTSUniqueSiteConstraint extends Constraint {

  public $message = 'This site tag already exists on %bundle';

}
