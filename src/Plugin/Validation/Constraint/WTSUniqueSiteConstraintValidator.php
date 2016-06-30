<?php

namespace Drupal\membership_provider_wts\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class WTSUniqueSiteConstraintValidator extends ConstraintValidator {

  /**
   * @inheritDoc
   */
  public function validate($value, Constraint $constraint) {
    /* @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->context->getRoot()->getValue();
    $entity_type_id = $entity->getEntityTypeId();
    $id_key = $entity->getEntityType()->getKey('id');
    $instances = \membership_provider_wts_field_instances();
    foreach ($instances as $entity_type => $def) {
      foreach ($def as $field_name => $field_config) {
        $query = \Drupal::entityQuery($entity_type)->condition($field_name . '.sub_id', $value, '=');
        if ($entity_type == $entity_type_id) {
          $query->condition($id_key, (int) $entity->id(), '<>');
        }
        $result = $query->execute();
        if ($result) {
          $this->context->addViolation($constraint->message, ['%bundle' => $entity_type . ':' . $field_name]);
          return;
        }
      }
    }
  }

}
