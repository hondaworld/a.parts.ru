<?php

namespace App\Model\Contact\Entity\TownType;

use App\Model\Contact\Entity\Town\Town;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TownTypeRepository::class)
 * @ORM\Table(name="townTypes")
 */
class TownType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @var Town[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Contact\Entity\Town\Town", mappedBy="type")
     */
    private $towns;

    public function __construct(string $name_short, string $name)
    {
        $this->name_short = $name_short;
        $this->name = $name;
    }

    public function update(string $name_short, string $name)
    {
        $this->name_short = $name_short;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getTowns(): array
    {
        return $this->towns->toArray();
    }
}
