<?php

namespace App\Model\Card\Entity\Category;

use App\Model\Card\Entity\Group\ZapGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCategoryRepository::class)
 * @ORM\Table(name="zapCategory")
 */
class ZapCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="zapCategoryID")
     */
    private $zapCategoryID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var ZapGroup[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Group\ZapGroup", mappedBy="category")
     */
    private $groups;

    public function __construct(string $name, int $number)
    {
        $this->name = $name;
        $this->number = $number;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->zapCategoryID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function changeNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return ZapGroup[]|ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }


}
