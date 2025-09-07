<?php declare(strict_types=1);

namespace Codematic\Inci\Core\Content\Inci;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class InciEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $name = null;
    protected ?string $slug = null;
    protected ?string $alternativeNames = null;
    protected ?string $casNumber = null;
    protected ?string $polishName = null;
    protected ?string $description = null;
    protected ?string $mainFunctions = null;
    protected ?string $safetyInformation = null;
    protected ?int $rating = null;
    protected ?string $resources = null;
    protected ?bool $natural = null;
    protected ?bool $active = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getAlternativeNames(): ?string
    {
        return $this->alternativeNames;
    }

    public function setAlternativeNames(?string $alternativeNames): void
    {
        $this->alternativeNames = $alternativeNames;
    }

    public function getCasNumber(): ?string
    {
        return $this->casNumber;
    }

    public function setCasNumber(?string $casNumber): void
    {
        $this->casNumber = $casNumber;
    }

    public function getPolishName(): ?string
    {
        return $this->polishName;
    }

    public function setPolishName(?string $polishName): void
    {
        $this->polishName = $polishName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getMainFunctions(): ?string
    {
        return $this->mainFunctions;
    }

    public function setMainFunctions(?string $mainFunctions): void
    {
        $this->mainFunctions = $mainFunctions;
    }

    public function getSafetyInformation(): ?string
    {
        return $this->safetyInformation;
    }

    public function setSafetyInformation(?string $safetyInformation): void
    {
        $this->safetyInformation = $safetyInformation;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): void
    {
        $this->rating = $rating;
    }


    public function getResources(): ?string
    {
        return $this->resources;
    }

    public function setResources(?string $resources): void
    {
        $this->resources = $resources;
    }

    public function isNatural(): ?bool
    {
        return $this->natural;
    }

    public function setNatural(?bool $natural): void
    {
        $this->natural = $natural;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }
}