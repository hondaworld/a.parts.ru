<?php

namespace App\Model\User\Entity\Opt;

use App\Model\Card\Entity\Opt\ZapCardOpt;
use App\Model\Provider\Entity\Opt\ProviderPriceOpt;
use App\Model\Sklad\Entity\Opt\PriceListOpt;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OptRepository::class)
 * @ORM\Table(name="opt")
 */
class Opt
{
    public const DEFAULT_OPT_ID = 1;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="optID")
     */
    private $optID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="opt")
     */
    private $users;

    /**
     * @var ProviderPriceOpt[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\Opt\ProviderPriceOpt", mappedBy="opt", orphanRemoval=true)
     */
    private $provider_price_profits;

    /**
     * @var PriceListOpt[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Sklad\Entity\Opt\PriceListOpt", mappedBy="opt", orphanRemoval=true)
     */
    private $price_list_profits;

    /**
     * @var ZapCardOpt[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Opt\ZapCardOpt", mappedBy="opt", orphanRemoval=true)
     */
    private $zapCard_profits;

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
        return $this->optID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function getNoneDelete(): ?bool
    {
        return $this->noneDelete;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unhide(): void
    {
        $this->isHide = false;
    }

    public function getUsers(): array
    {
        return $this->users->toArray();
    }

    public function changeNumber(int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return ProviderPriceOpt[]|array
     */
    public function getProviderPriceProfits(): array
    {
        return $this->provider_price_profits->toArray();
    }

    /**
     * @return PriceListOpt[]|array
     */
    public function getPriceListProfits(): array
    {
        return $this->price_list_profits->toArray();
    }

    /**
     * @return ZapCardOpt[]|array
     */
    public function getZapCardProfits(): array
    {
        return $this->zapCard_profits->toArray();
    }


}
