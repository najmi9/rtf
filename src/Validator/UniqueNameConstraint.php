<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueNameConstraint extends Constraint
{
    public $message = 'The organization "{{ name }}" already exists';
}
