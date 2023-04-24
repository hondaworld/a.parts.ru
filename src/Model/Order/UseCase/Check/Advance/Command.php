<?php

namespace App\Model\Order\UseCase\Check\Advance;

use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $balanceID;

    public $managerID;

    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, местоположение кассы"
     * )
     */
    public $zapSkladID;

    public function __construct(int $balanceID, int $managerID)
    {
        $this->managerID = $managerID;
        $this->balanceID = $balanceID;
    }
}
