<?php

namespace App\Model\Work\Entity\Category;

use App\Model\Work\Entity\Group\WorkGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WorkCategoryRepository::class)
 * @ORM\Table(name="workCategory")
 */
class WorkCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="workCategoryID")
     */
    private $workCategoryID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var WorkGroup[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Group\WorkGroup", mappedBy="category")
     * @ORM\OrderBy({"sort" = "ASC"})
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

    public function getId(): int
    {
        return $this->workCategoryID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function changeNumber(int $sort): void
    {
        $this->number = $sort;
    }

    /**
     * @return WorkGroup[]|ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }
}
