<?php

namespace App\Model\User\UseCase\User\Review;

use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var string
     */
    public $reviewUrl;

    public $isReviewSend;

    public $isReview;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->reviewUrl = $user->getReview()->getReviewUrl();
        $command->isReview = $user->getReview()->isReview();
        $command->isReviewSend = $user->getReview()->isReviewSend();
        return $command;
    }
}
