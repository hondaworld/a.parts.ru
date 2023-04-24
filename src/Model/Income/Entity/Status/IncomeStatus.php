<?php

namespace App\Model\Income\Entity\Status;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity(repositoryClass=IncomeStatusRepository::class)
 * @ORM\Table(name="income_statuses")
 */
class IncomeStatus
{
    public const DEFAULT_STATUS = 1;
    public const ORDERED = 2;
    public const INCREASING_DELIVERY_TIME = 3;
    public const FAILURE_USER = 4;
    public const FAILURE_PROVIDER = 5;
    public const IN_PATH = 6;
    public const PURCHASED = 7;
    public const IN_WAREHOUSE = 8;
    public const INCOME_IN_WAREHOUSE = 9;
    public const OUT_OF_STOCK = 10;
    public const IN_WORK = 11;

    public const ARR_IN_PATH = [2, 6, 7, 9];
    public const ARR_DELETED = [4, 5, 10];
    public const ARR_NOT_SEND = [1, 2];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="status")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="boolean", name="isEmail")
     */
    private $isEmail;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="status")
     */
    private $incomes;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="lastIncomeStatus")
     */
    private $order_goods;

    public function __construct(int $status = self::DEFAULT_STATUS)
    {
        Assert::oneOf($status, [
            self::DEFAULT_STATUS,
            self::ORDERED,
            self::INCREASING_DELIVERY_TIME,
            self::FAILURE_USER,
            self::FAILURE_PROVIDER,
            self::IN_PATH,
            self::PURCHASED,
            self::IN_WAREHOUSE,
            self::INCOME_IN_WAREHOUSE,
            self::OUT_OF_STOCK,
            self::IN_WORK,
        ]);

        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->status;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function isEmail(): ?bool
    {
        return $this->isEmail;
    }

    public function isNew(): bool
    {
        return $this->status == self::DEFAULT_STATUS;
    }

    public function isDeleted(): bool
    {
        return in_array($this->status, self::ARR_DELETED);
    }

    public function isInWarehouse(): bool
    {
        return $this->status == self::IN_WAREHOUSE;
    }

    public function isInIncomingOnWarehouse(): bool
    {
        return $this->status == self::INCOME_IN_WAREHOUSE;
    }

    public function isOnTheWay(): bool
    {
        return $this->status == self::IN_PATH;
    }

    public function isFailByUser(): bool
    {
        return $this->status == self::FAILURE_USER;
    }

    public function isOnTheWayOrInIncomingOnWarehouse(): bool
    {
        return in_array($this->status, [self::IN_PATH, self::INCOME_IN_WAREHOUSE]);
    }

    public function isOrdered(): bool
    {
        return $this->status == self::ORDERED;
    }

    public function isProcessed(): bool
    {
        return $this->status == self::IN_WORK;
    }

    public function verifyNewStatus(DetailNumber $number, IncomeStatus $status): ?string
    {
        if ($this->isInIncomingOnWarehouse() && !$status->isOrdered() && !$status->isOnTheWay()) {
            return "Деталь " . $number->getValue() . " уже на складе";
        }
        if ($this->isInWarehouse()) {
            return "Деталь " . $number->getValue() . " уже на складе";
        }
        if ($this->isProcessed()) {
            return "Статус детали " . $number->getValue() . " меняется в разделе Приходы->Заказы";
        }
        return null;
    }
}
