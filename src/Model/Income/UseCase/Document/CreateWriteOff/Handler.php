<?php

namespace App\Model\Income\UseCase\Document\CreateWriteOff;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Document\Document;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Document\IncomeDocumentRepository;
use App\Model\Income\Entity\Document\Osn;
use App\Model\Income\Entity\Good\IncomeGood;
use App\Model\Income\Entity\Good\IncomeGoodRepository;
use App\Model\Income\Entity\Sklad\IncomeSkladRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Income\IncomeDocumentFetcher;
use Doctrine\DBAL\Exception;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private IncomeDocumentFetcher $incomeDocumentFetcher;
    private IncomeDocumentRepository $incomeDocumentRepository;
    private IncomeSkladRepository $incomeSkladRepository;
    private DocumentTypeRepository $documentTypeRepository;

    public function __construct(
        DocumentTypeRepository $documentTypeRepository,
        IncomeDocumentFetcher $incomeDocumentFetcher,
        IncomeDocumentRepository $incomeDocumentRepository,
        IncomeSkladRepository $incomeSkladRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeDocumentFetcher = $incomeDocumentFetcher;
        $this->incomeDocumentRepository = $incomeDocumentRepository;
        $this->incomeSkladRepository = $incomeSkladRepository;
        $this->documentTypeRepository = $documentTypeRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $isReturn = false;
        foreach ($command->incomeSklads as $value) {
            if (intval($value) != 0) $isReturn = true;
        }

        if (!$isReturn) {
            throw new DomainException('Не указано количество для списания');
        }

        foreach ($command->incomeSklads as $incomeSkladID => $quantity) {

            if ($quantity > 0) {
                $incomeSklad = $this->incomeSkladRepository->get($incomeSkladID);

                $income = $incomeSklad->getIncome();

                $income->returnQuantity($quantity);
                $incomeSklad->returnQuantity($quantity);

                $firm = $income->getFirm();

                try {
                    $document_num = $this->incomeDocumentFetcher->getNextWON($firm->getId());
                } catch (Exception $e) {
                    throw new DomainException($e->getMessage());
                }

                $document = new Document($document_num, $command->document_prefix, $command->document_sufix);
                $osn = new Osn();

                $incomeDocument = new IncomeDocument($this->documentTypeRepository->get(DocumentType::WON), $document, $manager, null, null, null, $firm, $osn);
                $incomeDocument->assignIncomeGood($income, $incomeSklad, $manager, $quantity, $command->returning_reason);
                $this->incomeDocumentRepository->add($incomeDocument);
            }
        }

        $this->flusher->flush();
        return $messages;
    }
}
