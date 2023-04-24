<?php

namespace App\Model\Card\Entity\Photo;

use App\Model\Card\Entity\Card\ZapCard;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardPhotoRepository::class)
 * @ORM\Table(name="zapPhotos")
 */
class ZapCardPhoto
{
    public const PHOTO_SMALL_MAX_WIDTH = 150;
    public const PHOTO_SMALL_MAX_HEIGHT = 150;

    public const PHOTO_BIG_MAX_WIDTH = 1600;
    public const PHOTO_BIG_MAX_HEIGHT = 1600;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="zapPhotoID")
     */
    private $zapPhotoID;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="photos")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $simage;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bimage;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    public function __construct(ZapCard $zapCard, string $simage, string $bimage, bool $isMain)
    {
        $this->zapCard = $zapCard;
        $this->simage = $simage;
        $this->bimage = $bimage;
        $this->isMain = $isMain;
    }

    public function updateMain(bool $isMain)
    {
        $this->isMain = $isMain;
    }

    public function getId(): ?int
    {
        return $this->zapPhotoID;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getSimage(): ?string
    {
        return $this->simage;
    }

    public function getBimage(): ?string
    {
        return $this->bimage;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }
}
