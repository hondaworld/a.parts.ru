<?php

namespace App\Model\Firm\UseCase\Schet\Pay;

use App\Model\Firm\Entity\Schet\Schet;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $schetID;

    /**
     * @Assert\NotBlank()
     * @Assert\Positive ()
     */
    public $summ;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofpaid;

    public $isEmail;

    public function __construct(int $schetID)
    {
        $this->schetID = $schetID;
    }

    public static function fromEntity(Schet $schet): self
    {
        $command = new self($schet->getId());
        $command->dateofpaid = new \DateTime();
        return $command;
    }
}
