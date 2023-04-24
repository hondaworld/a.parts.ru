<?php

namespace App\Model\Reseller\UseCase\AvitoNotice\Edit;

use App\Model\Flusher;
use App\Model\Reseller\Entity\Avito\AvitoNoticeRepository;

class Handler
{
    private Flusher $flusher;
    private AvitoNoticeRepository $avitoNoticeRepository;

    public function __construct(AvitoNoticeRepository $avitoNoticeRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->avitoNoticeRepository = $avitoNoticeRepository;
    }

    public function handle(Command $command): void
    {
        $avitoNotice = $this->avitoNoticeRepository->get($command->id);

        $avitoNotice->update(
            $command->avito_id,
            $command->contact_phone,
            $command->address,
            $command->title,
            $command->description,
            $command->type_id,
            implode(' | ', explode("\n", $command->image_urls)),
            $command->make,
            $command->model,
            $command->generation,
            $command->modification
        );
        $this->flusher->flush();

    }
}
