<?php

namespace App\Model\Expense\UseCase\SchetFakKor\Create;

use App\Model\Expense\Entity\SchetFak\SchetFakRepository;
use App\Model\Expense\Entity\SchetFakKor\Document;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKorRepository;
use App\Model\Flusher;
use App\ReadModel\Expense\SchetFakKorFetcher;

class Handler
{
    private Flusher $flusher;
    private SchetFakKorRepository $schetFakKorRepository;
    private SchetFakKorFetcher $schetFakKorFetcher;
    private SchetFakRepository $schetFakRepository;

    public function __construct(
        SchetFakKorRepository    $schetFakKorRepository,
        SchetFakKorFetcher       $schetFakKorFetcher,
        SchetFakRepository       $schetFakRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetFakKorRepository = $schetFakKorRepository;
        $this->schetFakKorFetcher = $schetFakKorFetcher;
        $this->schetFakRepository = $schetFakRepository;
    }

    public function handle(Command $command): SchetFakKor
    {
        $schetFak = $this->schetFakRepository->get($command->schet_fakID);

        $document_num = $this->schetFakKorFetcher->getNext($schetFak->getFirm());

        $schetFakKor = new SchetFakKor(
            new Document($document_num, $command->document_prefix, $command->document_sufix),
            $schetFak->getFirm(),
            $schetFak
        );
        $this->schetFakKorRepository->add($schetFakKor);

        $this->flusher->flush();

        return $schetFakKor;
    }
}
