<?php

namespace App\Model\Income\Entity\Order;

use App\Model\Income\Entity\Income\Income;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncomeOrderRepository::class)
 * @ORM\Table(name="incomeOrders")
 */
class IncomeOrder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="incomeOrderID")
     */
    private $incomeOrderID;

    /**
     * @var Provider
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Provider\Provider", inversedBy="income_orders")
     * @ORM\JoinColumn(name="providerID", referencedColumnName="providerID", nullable=false)
     */
    private $provider;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="income_orders")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @ORM\Column(type="integer")
     */
    private $document_num;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="integer", name="isOrdered")
     */
    private $isOrdered = 0;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="income_order")
     */
    private $incomes;

    public function __construct(Provider $provider, ZapSklad $zapSklad, int $document_num)
    {
        $this->provider = $provider;
        $this->zapSklad = $zapSklad;
        $this->document_num = $document_num;
        $this->dateofadded = new \DateTime();
    }

    public function ordered()
    {
        $this->isOrdered = 1;
    }

    public function deleted()
    {
        $this->isOrdered = 2;
    }

    public function getId(): ?int
    {
        return $this->incomeOrderID;
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getDocumentNum(): ?int
    {
        return $this->document_num;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getIsOrdered(): ?int
    {
        return $this->isOrdered;
    }

    public function isNotOrdered(): bool
    {
        return $this->isOrdered == 0;
    }

    public function isOrdered(): bool
    {
        return $this->isOrdered == 1;
    }

    public function isDeleted(): bool
    {
        return $this->isOrdered == 2;
    }

    /**
     * @return Income[]|ArrayCollection
     */
    public function getIncomes()
    {
        return $this->incomes->toArray();
    }

    public function removeIncome(Income $income)
    {
        $this->incomes->removeElement($income);
    }

    public function getMailSubject(): string
    {
        if ($this->zapSklad->getId() == 5) {
            $subject = $this->provider->getIncomeOrderSubject5() != '' ? $this->provider->getIncomeOrderSubject5() : "Заказ от Parts.ru";
        } else {
            $subject = $this->provider->getIncomeOrderSubject() != '' ? $this->provider->getIncomeOrderSubject() : "Заказ от Parts.ru";
        }
        return str_ireplace("{document_num}", $this->document_num, $subject);
    }

    public function getMailText(): string
    {
        if ($this->zapSklad->getId() == 5) {
            $text = $this->provider->getIncomeOrderText5();
        } else {
            $text = $this->provider->getIncomeOrderText();
        }
        return str_ireplace("{document_num}", $this->document_num, $text);
    }

}
