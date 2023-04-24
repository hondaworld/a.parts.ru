<?php

namespace App\Model\Beznal\Entity\Bank;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\User\Entity\FirmContr\FirmContr;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BankRepository::class)
 * @ORM\Table(name="banks")
 */
class Bank
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="bankID")
     */
    private $bankID;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $bik;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $korschet;

    /**
     * @ORM\Column(type="text")
     */
    private $address;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var Beznal[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", mappedBy="bank")
     */
    private $beznals;

    /**
     * @var FirmContr[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\FirmContr\FirmContr", mappedBy="bank")
     */
    private $firmcontr;

    public function __construct(string $bik, string $name, string $korschet, string $address, string $description)
    {
        $this->bik = $bik;
        $this->name = $name;
        $this->korschet = $korschet;
        $this->address = $address;
        $this->description = $description;
    }

    public function update(string $bik, string $name, string $korschet, string $address, string $description)
    {
        $this->bik = $bik;
        $this->name = $name;
        $this->korschet = $korschet;
        $this->address = $address;
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->bankID;
    }

    public function getBik(): ?string
    {
        return $this->bik;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getKorschet(): ?string
    {
        return $this->korschet;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    /**
     * @return Beznal[]|ArrayCollection
     */
    public function getBeznals()
    {
        return $this->beznals->toArray();
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }
}
