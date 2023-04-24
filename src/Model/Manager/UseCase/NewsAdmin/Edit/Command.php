<?php

namespace App\Model\Manager\UseCase\NewsAdmin\Edit;

use App\Model\Manager\Entity\NewsAdmin\NewsAdmin;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $newsID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public function __construct(int $newsID)
    {
        $this->newsID = $newsID;
    }

    public static function fromEntity(NewsAdmin $newsAdmin): self
    {
        $command = new self($newsAdmin->getId());
        $command->name = $newsAdmin->getName();
        $command->description = $newsAdmin->getDescription();
        $command->type = $newsAdmin->getType();
        $command->dateofadded = $newsAdmin->getDateofadded();
        return $command;
    }
}
