<?php

namespace App\Model\Auto\UseCase\Generation\Photo;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $autoGenerationID;

    /**
     * @var int
     */
    public $autoModelID;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $photo;

    public function __construct(int $autoGenerationID)
    {
        $this->autoGenerationID = $autoGenerationID;
    }

    public static function fromEntity(AutoGeneration $autoGeneration, string $photoDirectory): self
    {
        $command = new self($autoGeneration->getId());
        $command->autoModelID = $autoGeneration->getModel()->getId();
        $command->photo = $autoGeneration->getPhoto() ? $photoDirectory . $autoGeneration->getPhoto() : '';
        return $command;
    }
}
