<?php

namespace App\Model\Card\UseCase\Category\Edit;

use App\Model\Card\Entity\Category\ZapCategory;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCategoryID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $zapCategoryID)
    {
        $this->zapCategoryID = $zapCategoryID;
    }

    public static function fromEntity(ZapCategory $zapCategory): self
    {
        $command = new self($zapCategory->getId());
        $command->name = $zapCategory->getName();
        return $command;
    }
}
