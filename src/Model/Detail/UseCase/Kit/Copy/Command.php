<?php

namespace App\Model\Detail\UseCase\Kit\Copy;

use App\Model\Detail\Entity\Kit\ZapCardKit;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $copyID;

    public function __construct(ZapCardKit $zapCardKit)
    {
        $this->copyID = $zapCardKit->getId();
    }
}
