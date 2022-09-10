<?php

declare(strict_types=1);

namespace App\Validator;

use App\Model\Organization;
use App\Repository\OrganizationRepository;
use App\Validator\UniqueNameConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueNameConstraintValidator extends ConstraintValidator
{
    private OrganizationRepository $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueNameConstraint) {
            throw new UnexpectedTypeException($constraint, UniqueNameConstraint::class);
        }

        if (!is_string($value)) {
            return;
        }

        $isAlreadyExists = array_filter(
            $this->organizationRepository->findAll(),
            fn (Organization $organization) => $organization->getName() === $value
        );

        if (false === empty($isAlreadyExists)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ name }}', $value)
                ->addViolation();
        }
    }
}
