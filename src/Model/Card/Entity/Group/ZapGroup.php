<?php

namespace App\Model\Card\Entity\Group;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Category\ZapCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapGroupRepository::class)
 * @ORM\Table(name="zapGroup")
 */
class ZapGroup
{
    public const PHOTO_MAX_WIDTH = 150;
    public const PHOTO_MAX_HEIGHT = 150;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="zapGroupID")
     */
    private $zapGroupID;

    /**
     * @var ZapCategory
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Category\ZapCategory", inversedBy="groups")
     * @ORM\JoinColumn(name="zapCategoryID", referencedColumnName="zapCategoryID", nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo = '';

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="zapGroup")
     */
    private $zapCards;

    public function __construct(string $name, ZapCategory $zapCategory)
    {
        $this->name = $name;
        $this->category = $zapCategory;
    }

    public function update(string $name, ZapCategory $zapCategory)
    {
        $this->name = $name;
        $this->category = $zapCategory;
    }

    public function updatePhoto(string $photo)
    {
        $this->photo = $photo;
    }

    public function getId(): ?int
    {
        return $this->zapGroupID;
    }

    public function getZapCategory(): ZapCategory
    {
        return $this->category;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function removePhoto(): void
    {
        $this->photo = '';
    }

    /**
     * @return ZapCard[]|array
     */
    public function getZapCards(): array
    {
        return $this->zapCards->toArray();
    }


}
