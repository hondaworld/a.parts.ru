<?php

namespace App\Model\User\UseCase\TemplateGroup\Create;

use App\Model\Flusher;
use App\Model\User\Entity\ShopPayType\ShopPayType;
use App\Model\User\Entity\ShopPayType\ShopPayTypeRepository;
use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use App\Model\User\Entity\TemplateGroup\TemplateGroupRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(TemplateGroupRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $templateGroup = new TemplateGroup($command->name);

        $this->repository->add($templateGroup);

        $this->flusher->flush();
    }
}
