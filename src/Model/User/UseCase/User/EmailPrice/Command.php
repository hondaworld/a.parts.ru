<?php

namespace App\Model\User\UseCase\User\EmailPrice;

use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var string
     */
    public $email_price;

    /**
     * @Assert\Choice({0, 1, 5})
     */
    public $zapSkladID;

    public $isPrice;

    public $isPriceSummary;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->email_price = $user->getEmailPrice()->getValue();
        $command->zapSkladID = $user->getEmailPrice()->getZapSkladID();
        $command->isPrice = $user->getEmailPrice()->isPrice();
        $command->isPriceSummary = $user->getEmailPrice()->isPriceSummary();
        return $command;
    }
}
