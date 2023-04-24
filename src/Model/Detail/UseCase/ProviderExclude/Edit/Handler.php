<?php

namespace App\Model\Detail\UseCase\ProviderExclude\Edit;

use App\Model\Detail\Entity\ProviderExclude\DetailProviderExcludeRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $detailProviderExcludeRepository;

    public function __construct(DetailProviderExcludeRepository $detailProviderExcludeRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->detailProviderExcludeRepository = $detailProviderExcludeRepository;
    }

    public function handle(Command $command): void
    {
        $detailProviderExclude = $this->detailProviderExcludeRepository->get($command->excludeID);

        $detailProviderExclude->update(
            $command->comment
        );

        $this->flusher->flush();
    }
}
