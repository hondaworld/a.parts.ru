<?php

namespace App\Model\Firm\UseCase\Schet\PayUrl;

use App\Model\Firm\Entity\Schet\Schet;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $schetID;

    public $pay_url;

    public function __construct(int $schetID)
    {
        $this->schetID = $schetID;
    }

    public static function fromEntity(Schet $schet): self
    {
        $command = new self($schet->getId());
        $command->pay_url = $schet->getPayUrl();
        return $command;
    }
}
