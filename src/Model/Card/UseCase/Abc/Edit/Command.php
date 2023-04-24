<?php

namespace App\Model\Card\UseCase\Abc\Edit;

use App\Model\Card\Entity\Abc\Abc;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $abcID;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="2",
     *     minMessage="Краткое наименование должно быть не больше 2 символа"
     * )
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9]+$/",
     *     message="Значение должна быть латинской буквой или цифрой"
     * )
     */
    public $abc;

    /**
     * @var string
     */
    public $description;

    public function __construct(int $abcID)
    {
        $this->abcID = $abcID;
    }

    public static function fromEntity(Abc $abc): self
    {
        $command = new self($abc->getId());
        $command->abc = $abc->getAbc();
        $command->description = $abc->getDescription();
        return $command;
    }
}
