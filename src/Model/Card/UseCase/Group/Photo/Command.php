<?php

namespace App\Model\Card\UseCase\Group\Photo;

use App\Model\Card\Entity\Group\ZapGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $zapGroupID;

    /**
     * @var int
     */
    public $zapCategoryID;

    /**
     * @var string
     */
    public $photo;

    public function __construct(int $zapGroupID)
    {
        $this->zapGroupID = $zapGroupID;
    }

    public static function fromEntity(ZapGroup $zapGroup, string $photoDirectory): self
    {
        $command = new self($zapGroup->getId());
        $command->zapCategoryID = $zapGroup->getZapCategory()->getId();
        $command->photo = $zapGroup->getPhoto() ? $photoDirectory . $zapGroup->getPhoto() : '';
        return $command;
    }
}
