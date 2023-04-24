<?php


namespace App\Model\Order\Entity\Good;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class LastIncomeStatusData
{
    /**
     * @ORM\Column(type="boolean", name="lastIncomeStatusEmailed")
     */
    private $lastIncomeStatusEmailed = true;

    /**
     * @ORM\Column(type="datetime", name="lastIncomeStatusDate")
     */
    private $lastIncomeStatusDate;

    public function __construct(bool $lastIncomeStatusEmailed = true, ?\DateTime $lastIncomeStatusDate = null)
    {
        $this->lastIncomeStatusEmailed = $lastIncomeStatusEmailed;
        $this->lastIncomeStatusDate = $lastIncomeStatusDate ?: new \DateTime();
    }

    public function emailed(): void
    {
        $this->lastIncomeStatusEmailed = true;
    }

    /**
     * @return bool
     */
    public function isLastIncomeStatusEmailed(): bool
    {
        return $this->lastIncomeStatusEmailed;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastIncomeStatusDate(): ?\DateTime
    {
        return $this->lastIncomeStatusDate;
    }

}