<?php

namespace App\Model\Work\UseCase\Category\Edit;

use App\Model\Work\Entity\Category\WorkCategory;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromEntity(WorkCategory $workCategory): self
    {
        $command = new self($workCategory->getId());
        $command->name = $workCategory->getName();
        return $command;
    }
}
