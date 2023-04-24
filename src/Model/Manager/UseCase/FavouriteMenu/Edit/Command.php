<?php

namespace App\Model\Manager\UseCase\FavouriteMenu\Edit;

use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu;
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
     * @Assert\Length(
     *     max="30",
     *     maxMessage="Максимум 30 символов"
     * )
     */
    public $name;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromEntity(FavouriteMenu $favouriteMenu): self
    {
        $command = new self($favouriteMenu->getId());
        $command->name = $favouriteMenu->getName();
        return $command;
    }
}
