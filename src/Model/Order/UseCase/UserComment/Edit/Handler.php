<?php

namespace App\Model\Order\UseCase\UserComment\Edit;

use App\Model\Flusher;
use App\Model\User\Entity\Comment\UserCommentRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(UserCommentRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $userComment = $this->repository->get($command->commentID);
        $userComment->update($command->comment);
        $this->flusher->flush();
    }
}
