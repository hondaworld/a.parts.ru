<?php

namespace App\Model\Auto\UseCase\WorkPeriod\Copy;

use App\Model\Auto\Entity\Modification\AutoModification;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_modificationID;

    /**
     * @Assert\NotBlank()
     */
    public $copy_auto_modificationID;

    public function __construct(AutoModification $auto_modification)
    {
        $this->auto_modificationID = $auto_modification->getId();
    }
}
