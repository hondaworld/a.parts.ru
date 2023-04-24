<?php

namespace App\Model\User\UseCase\Template\Create;

use App\Model\Flusher;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\TemplateGroup\TemplateGroupRepository;

class Handler
{
    private $repository;
    private $flusher;
    private TemplateGroupRepository $templateGroupRepository;

    public function __construct(TemplateRepository $repository, TemplateGroupRepository $templateGroupRepository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->templateGroupRepository = $templateGroupRepository;
    }

    public function handle(Command $command): void
    {
        $template = new Template($this->templateGroupRepository->get($command->templateGroupID), $command->name, $command->subject, $command->text);

        $this->repository->add($template);

        $this->flusher->flush();
    }
}
