<?php

namespace App\Model\Finance\Entity\FinanceType;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FinanceTypeRepository::class)
 * @ORM\Table(name="finance_types")
 */
class FinanceType
{
    public const DEFAULT_BEZNAL_ID = 4;
    public const DEFAULT_BEZNAL_CARD_ID = 5;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="finance_typeID")
     */
    private $finance_typeID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="finance_types")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=false)
     */
    private $firm;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @var UserBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\BalanceHistory\UserBalanceHistory", mappedBy="finance_type")
     */
    private $balance_history;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="finance_type")
     */
    private $expenseDocuments;

    /**
     * @var Schet[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Schet\Schet", mappedBy="finance_type")
     */
    private $schets;

    public function __construct(string $name, Firm $firm, bool $isMain)
    {
        $this->name = $name;
        $this->firm = $firm;
        $this->isMain = $isMain;
        $this->schets = new ArrayCollection();
    }

    public function update(string $name, Firm $firm, bool $isMain)
    {
        $this->name = $name;
        $this->firm = $firm;
        $this->isMain = $isMain;
    }

    public function getId(): ?int
    {
        return $this->finance_typeID;
    }

    public function isBeznal(): bool
    {
        return $this->getId() === self::DEFAULT_BEZNAL_ID;
    }

    public function isCreditCard(): bool
    {
        return $this->getId() === self::DEFAULT_BEZNAL_CARD_ID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getFirm(): Firm
    {
        return $this->firm;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
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
     * @return UserBalanceHistory[]|array
     */
    public function getBalanceHistory(): array
    {
        return $this->balance_history->toArray();
    }

//    /**
//     * @return Collection|Schet[]
//     */
//    public function getSchets(): Collection
//    {
//        return $this->schets;
//    }
//
//    public function addSchet(Schet $schet): self
//    {
//        if (!$this->schets->contains($schet)) {
//            $this->schets[] = $schet;
//            $schet->setFinanceType($this);
//        }
//
//        return $this;
//    }
//
//    public function removeSchet(Schet $schet): self
//    {
//        if ($this->schets->removeElement($schet)) {
//            // set the owning side to null (unless already changed)
//            if ($schet->getFinanceType() === $this) {
//                $schet->setFinanceType(null);
//            }
//        }
//
//        return $this;
//    }


}
