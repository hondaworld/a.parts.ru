<?php

namespace App\Model\Finance\UseCase\NalogNds\Create;

use App\Model\Finance\Entity\Nalog\Nalog;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $nds;

    public $nalog;

    public function __construct(Nalog $nalog)
    {
        $this->nalog = $nalog;
        $this->dateofadded = new \DateTime();
    }
}
