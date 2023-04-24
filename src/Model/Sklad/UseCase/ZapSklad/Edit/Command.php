<?php

namespace App\Model\Sklad\UseCase\ZapSklad\Edit;

use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapSkladID;

    /**
     * @Assert\NotBlank()
     */
    public $name_short;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $isTorg;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $koef;

    public $optID;

    public $isMain;

    public function __construct(int $zapSkladID)
    {
        $this->zapSkladID = $zapSkladID;
    }

    public static function fromEntity(ZapSklad $zapSklad): self
    {
        $command = new self($zapSklad->getId());
        $command->name_short = $zapSklad->getNameShort();
        $command->name = $zapSklad->getName();
        $command->isTorg = $zapSklad->isTorg();
        $command->koef = $zapSklad->getKoef();
        $command->optID = $zapSklad->getOpt() ? $zapSklad->getOpt()->getId() : null;
        $command->isMain = $zapSklad->isMain();
        return $command;
    }
}
