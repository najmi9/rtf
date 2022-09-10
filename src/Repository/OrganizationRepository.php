<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Organization;
use App\Service\OrganizationsYamlService;

class OrganizationRepository
{
    private OrganizationsYamlService $organizationsYamlService;

    public function __construct(
        OrganizationsYamlService $organizationsYamlService
    ) {
        $this->organizationsYamlService = $organizationsYamlService;
    }

    public function persist(Organization $organization): void
    {
        $organizations = $this->findAll(true);

        $organizations[] = [
            'name' => $organization->getName(),
            'description' => $organization->getDescription(),
        ];

        $this->organizationsYamlService->write($organizations);
    }

    public function updateByName(string $name, Organization $organization): void
    {
        $organizations = array_filter(
            $this->findAll(true),
            fn (array $org) => $org['name'] !== $name
        );

        $organizations[] = [
            'name' => $organization->getName(),
            'description' => $organization->getDescription(),
        ];

        $this->organizationsYamlService->write($organizations);
    }

    public function removeByName(string $name): void
    {
        $organizations = $this->findAll(true);
        $filteredOrganizations = array_filter(
            $organizations,
            fn (array $item) => $item['name'] !== $name
        );
        $this->organizationsYamlService->write($filteredOrganizations);
    }

    public function findOneByName(string $name): ?Organization
    {
        $organizations = $this->findAll();

        $filteredOrganizations = array_filter(
            $organizations,
            fn (Organization $organization) => $organization->getName() === $name
        );

        if (!empty($filteredOrganizations)) {
            return end($filteredOrganizations);
        }

        return null;
    }

    /**
     * @return Organization[]|array
     */
    public function findAll(bool $returnAsArray = false): array
    {
        $organizations = $this->organizationsYamlService->read();

        if ($returnAsArray) {
            return $organizations;
        }

        return array_map(
            fn (array $organization) => (new Organization())
                ->setName($organization['name'])
                ->setDescription($organization['description']),
            $organizations
        );
    }
}
