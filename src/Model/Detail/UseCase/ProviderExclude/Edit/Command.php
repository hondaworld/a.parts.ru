<?php

namespace App\Model\Detail\UseCase\ProviderExclude\Edit;

use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $excludeID;

    public $comment;

    public function __construct(int $excludeID)
    {
        $this->excludeID = $excludeID;
    }

    public static function fromEntity(DetailProviderExclude $detailProviderExclude): self
    {
        $command = new self($detailProviderExclude->getId());
        $command->comment = $detailProviderExclude->getComment();
        return $command;
    }
}
