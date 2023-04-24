<?php

namespace App\Model\Provider\UseCase\Provider\Send;

use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\UseCase\Provider\User;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerID;

    /**
     * @var boolean
     */
    public $isIncomeOrderAutoSend;

    /**
     * @var array
     */
    public $incomeOrderWeekDays;

    /**
     * @var \DateTime
     */
    public $incomeOrderTime;

    public function __construct(int $providerID)
    {
        $this->providerID = $providerID;
    }

    public static function fromEntity(Provider $provider): self
    {
        $command = new self($provider->getId());
        $command->isIncomeOrderAutoSend = $provider->isIncomeOrderAutoSend();
        $command->incomeOrderWeekDays = $provider->getIncomeOrderWeekDays();
        $command->incomeOrderTime = new \DateTime(date('Y-m-d ') . ($provider->getIncomeOrderTime() ?: '00:00') . ':00');
        return $command;
    }
}
