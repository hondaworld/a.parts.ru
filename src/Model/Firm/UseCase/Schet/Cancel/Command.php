<?php

namespace App\Model\Firm\UseCase\Schet\Cancel;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $schetID;

    /**
     * @Assert\NotBlank()
     */
    public $cancelReason;

    public function __construct(int $schetID)
    {
        $this->schetID = $schetID;
    }
}
