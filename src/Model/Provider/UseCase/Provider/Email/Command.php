<?php

namespace App\Model\Provider\UseCase\Provider\Email;

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
     * @Assert\Positive(
     *     message="Значение должно быть целым числом"
     * )
     */
    public $incomeOrderNumber;

    /**
     * @var string
     */
    public $incomeOrderSubject;

    /**
     * @var string
     */
    public $incomeOrderText;

    /**
     * @var string
     */
    public $incomeOrderSubject5;

    /**
     * @var string
     */
    public $incomeOrderText5;

    /**
     * @var string
     */
    public $incomeOrderEmail;

    /**
     * @var boolean
     */
    public $isIncomeOrder;

    public function __construct(int $providerID)
    {
        $this->providerID = $providerID;
    }

    public static function fromEntity(Provider $provider): self
    {
        $command = new self($provider->getId());
        $command->incomeOrderEmail = $provider->getIncomeOrderEmail();
        $command->incomeOrderNumber = $provider->getIncomeOrderNumber();
        $command->incomeOrderSubject = $provider->getIncomeOrderSubject();
        $command->incomeOrderText = $provider->getIncomeOrderText();
        $command->incomeOrderSubject5 = $provider->getIncomeOrderSubject5();
        $command->incomeOrderText5 = $provider->getIncomeOrderText5();
        $command->isIncomeOrder = $provider->isIncomeOrder();
        return $command;
    }
}
