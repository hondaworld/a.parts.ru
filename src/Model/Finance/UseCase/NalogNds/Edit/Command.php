<?php

namespace App\Model\Finance\UseCase\NalogNds\Edit;

use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Finance\Entity\NalogNds\NalogNds;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $nalogNdsID;
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

    public function __construct(int $nalogNdsID)
    {
        $this->nalogNdsID = $nalogNdsID;
    }

    public static function fromNalogNds(NalogNds $nalogNds): self
    {
        $command = new self($nalogNds->getId());
        $command->dateofadded = $nalogNds->getDateofadded();
        $command->nds = $nalogNds->getNds();
        return $command;
    }
}
