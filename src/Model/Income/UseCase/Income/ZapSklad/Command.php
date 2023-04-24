<?php

namespace App\Model\Income\UseCase\Income\ZapSklad;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, склад"
     * )
     */
    public $zapSkladID;

    public $cols;
}
