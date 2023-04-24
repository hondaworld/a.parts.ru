<?php

namespace App\Model\Detail\UseCase\Kit\Edit;

use App\Model\Detail\Entity\Kit\ZapCardKit;
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

    public static function fromEntity(ZapCardKit $kit): self
    {
        $command = new self($kit->getId());
        $command->name = $kit->getName();
        return $command;
    }
}
