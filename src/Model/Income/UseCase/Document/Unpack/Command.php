<?php

namespace App\Model\Income\UseCase\Document\Unpack;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank(
     *     message="Выберите, пожалйуста, поставщика"
     * )
     */
    public $providerID;
}
