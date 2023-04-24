<?php

namespace App\Model\Auto\UseCase\Engine\Edit;

use App\Model\Auto\Entity\Engine\AutoEngine;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_engineID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $url;

    public $description_tuning;

    public function __construct(int $auto_engineID)
    {
        $this->auto_engineID = $auto_engineID;
    }

    public static function fromEntity(AutoEngine $autoEngine): self
    {
        $command = new self($autoEngine->getId());
        $command->name = $autoEngine->getName();
        $command->url = $autoEngine->getUrl();
        $command->description_tuning = $autoEngine->getDescriptionTuning();
        return $command;
    }
}
