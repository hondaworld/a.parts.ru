<?php

namespace App\Model\Provider\Entity\Group;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProviderPriceGroupRepository::class)
 * @ORM\Table(name="providerPriceGroups")
 */
class ProviderPriceGroup
{
    public const DEFAULT_ID = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="providerPriceGroupID")
     */
    private $providerPriceGroupID;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    public function getId(): int
    {
        return $this->providerPriceGroupID;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
