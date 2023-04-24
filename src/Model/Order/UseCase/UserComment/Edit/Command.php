<?php

namespace App\Model\Order\UseCase\UserComment\Edit;

use App\Model\User\Entity\Comment\UserComment;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $commentID;

    /**
     * @Assert\NotBlank(
     *     message="Пожалуйста, введите комментарий"
     * )
     */
    public $comment;

    public function __construct(int $commentID)
    {
        $this->commentID = $commentID;
    }

    public static function fromEntity(UserComment $userComment): self
    {
        $command = new self($userComment->getId());
        $command->comment = $userComment->getComment();
        return $command;
    }
}
