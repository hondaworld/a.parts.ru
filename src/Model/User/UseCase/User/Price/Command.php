<?php

namespace App\Model\User\UseCase\User\Price;

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
    public $email;

    /**
     * @var string
     */
    public $email_send;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var bool
     */
    public $first_line;

    /**
     * @var int
     */
    public $line;

    /**
     * @var int
     */
    public $order_num;

    /**
     * @var int
     */
    public $number_num;

    /**
     * @var int
     */
    public $creater_num;

    /**
     * @var int
     */
    public $quantity_num;

    /**
     * @var int
     */
    public $price_num;

    /**
     * @var int
     */
    public $createrID;

    /**
     * @var int
     */
    public $zapSkladID;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->email = $user->getPrice()->getEmail();
        $command->email_send = $user->getPrice()->getEmailSend();
        $command->filename = $user->getPrice()->getFilename();
        $command->first_line = $user->getPrice()->isFirstLine();
        $command->line = $user->getPrice()->getLine();
        $command->order_num = $user->getPrice()->getOrderNum();
        $command->number_num = $user->getPrice()->getNumberNum();
        $command->creater_num = $user->getPrice()->getCreaterNum();
        $command->quantity_num = $user->getPrice()->getQuantityNum();
        $command->price_num = $user->getPrice()->getPriceNum();
        $command->createrID = $user->getPriceCreater() ? $user->getPriceCreater()->getId() : null;
        $command->zapSkladID = $user->getPriceSklad() ? $user->getPriceSklad()->getId() : null;
        return $command;
    }
}
