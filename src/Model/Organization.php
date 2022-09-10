<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Organization
{
    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    private string $name;

    /**
     * @Assert\NotNull
     * @Assert\NotBlank
     * @Assert\Length(
     *    min=10,
     *    minMessage="Organization description too short",
     * )
     */
    private string $description;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
