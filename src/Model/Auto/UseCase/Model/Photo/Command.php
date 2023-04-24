<?php

namespace App\Model\Auto\UseCase\Model\Photo;

use App\Model\Auto\Entity\Model\AutoModel;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $autoModelID;

    /**
     * @var int
     */
    public $autoMarkaID;

    /**
     * @var string
     */
    public $photo;

    public function __construct(int $autoModelID)
    {
        $this->autoModelID = $autoModelID;
    }

    public static function fromEntity(AutoModel $autoModel, string $photoDirectory): self
    {
        $command = new self($autoModel->getId());
        $command->autoMarkaID = $autoModel->getMarka()->getId();
        $command->photo = $autoModel->getPhoto() ? $photoDirectory . $autoModel->getPhoto() : '';
        return $command;
    }
}
