<?php

namespace App\Model\User\UseCase\TemplateGroup\Edit;

use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $templateGroupID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $templateGroupID)
    {
        $this->templateGroupID = $templateGroupID;
    }

    public static function fromEntity(TemplateGroup $templateGroup): self
    {
        $command = new self($templateGroup->getId());
        $command->name = $templateGroup->getName();
        return $command;
    }
}
