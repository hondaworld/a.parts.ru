<?php


namespace App\Model\User\Entity\User;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Debt
{
    /**
     * @ORM\Column(type="integer", name="debts_days")
     */
    private $debts_days;

    /**
     * @ORM\Column(type="integer", name="debtInDays")
     */
    private $debtInDays;

    /**
     * @ORM\Column(type="date", name="debts_date")
     */
    private $debts_date;

    public function __construct(?int $debts_days = 3, ?int $debtInDays = 0)
    {
        $this->debts_days = $debts_days ?: 0;
        $this->debtInDays = $debtInDays ?: 0;
    }

    /**
     * @return int
     */
    public function getDebtsDays(): int
    {
        return $this->debts_days;
    }

    /**
     * @return int
     */
    public function getDebtInDays(): int
    {
        return $this->debtInDays;
    }

    /**
     * @return \DateTime|null
     */
    public function getDebtsDate(): ?\DateTime
    {
        if ($this->debts_date && $this->debts_date->format('Y') == '-0001') return null;
        return $this->debts_date;
    }

    /**
     * @param \DateTime|null $debts_date
     */
    public function setDebtsDate(?\DateTime $debts_date): void
    {
        $this->debts_date = $debts_date;
    }


}