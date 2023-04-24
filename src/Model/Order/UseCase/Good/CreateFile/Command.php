<?php

namespace App\Model\Order\UseCase\Good\CreateFile;

use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var bool
     */
    public $first_line;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $number_num;

    /**
     * @var int
     */
    public $creater_num;

    /**
     * @var int
     * @Assert\NotBlank()
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

    /**
     * @var int
     */
    public $providerPriceID;

    /**
     * @var string
     */
    public $file;

    /**
     * @var array
     */
    public $quantities;

    /**
     * @var array
     */
    public $data;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromUser(User $user): self
    {
        $command = new self($user->getId());
        $command->first_line = $user->getPrice()->isFirstLine();
        $command->number_num = $user->getPrice()->getNumberNum();
        $command->creater_num = $user->getPrice()->getCreaterNum();
        $command->quantity_num = $user->getPrice()->getQuantityNum();
        $command->price_num = $user->getPrice()->getPriceNum();
        $command->createrID = $user->getPriceCreater() ? $user->getPriceCreater()->getId() : null;
        $command->zapSkladID = $user->getPriceSklad() ? $user->getPriceSklad()->getId() : null;
        return $command;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        if ($this->providerPriceID) {
            if (
                isset($this->quantities[$arr[0]]) &&
                isset($this->quantities[$arr[0]][$arr[1]])
            )
                return $this->quantities[$arr[0]][$arr[1]];
        } else if ($this->zapSkladID) {
            if (
                isset($this->quantities[$arr[0]])
            )
                return $this->quantities[$arr[0]];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        if ($this->providerPriceID) {
            $this->quantities[$arr[0]][$arr[1]] = $value;
        } else if ($this->zapSkladID) {
            $this->quantities[$arr[0]] = $value;
        }
    }
}
