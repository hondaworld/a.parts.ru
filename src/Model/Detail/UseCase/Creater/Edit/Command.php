<?php

namespace App\Model\Detail\UseCase\Creater\Edit;

use App\Model\Detail\Entity\Creater\Creater;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $name_rus;
    /**
     * @var boolean
     */
    public $isOriginal;

    /**
     * @var string
     */
    public $tableName;

    /**
     * @var int
     */
    public $creater_weightID;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $catalogs;

    /**
     * @var string
     */
    public $alt_names;

    public function __construct(int $createrID)
    {
        $this->createrID = $createrID;
    }

    public static function fromEntity(Creater $creater): self
    {
        $command = new self($creater->getId());
        $command->name = $creater->getName();
        $command->name_rus = $creater->getNameRus();
        $command->isOriginal = $creater->isOriginal();
        $command->tableName = $creater->getTableName();
        $command->creater_weightID = $creater->getCreaterWeight() ? $creater->getCreaterWeight()->getId() : null;
        $command->description = $creater->getDescription();
        $command->catalogs = $creater->getCatalogs();
        $command->alt_names = $creater->getAltNames();
        return $command;
    }
}
