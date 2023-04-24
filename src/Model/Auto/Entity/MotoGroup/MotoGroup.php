<?php

namespace App\Model\Auto\Entity\MotoGroup;

use App\Model\Auto\Entity\MotoModel\MotoModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MotoGroupRepository::class)
 * @ORM\Table(name="moto_group")
 */
class MotoGroup
{
    public const PHOTO_MAX_WIDTH = 350;
    public const PHOTO_MAX_HEIGHT = 120;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="moto_groupID")
     */
    private $moto_groupID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo = '';

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var MotoModel[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\MotoModel\MotoModel", mappedBy="group")
     */
    private $moto_models;

    public function __construct(string $name, ?string $photo)
    {
        $this->name = $name;
        $this->photo = $photo ?: '';
    }

    public function update(string $name, ?string $photo)
    {
        $this->name = $name;
        $this->photo = $photo ?: '';
    }

    public function getId(): ?int
    {
        return $this->moto_groupID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }

    /**
     * @return MotoModel[]|ArrayCollection
     */
    public function getMotoModels()
    {
        return $this->moto_models->toArray();
    }

    public function removePhoto(): void
    {
        $this->photo = '';
    }
}
