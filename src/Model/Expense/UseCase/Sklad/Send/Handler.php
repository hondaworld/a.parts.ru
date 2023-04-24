<?php

namespace App\Model\Expense\UseCase\Sklad\Send;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Expense\Entity\SkladDocument\Document;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocumentRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Expense\ExpenseSkladDocumentFetcher;

class Handler
{
    private Flusher $flusher;
    private ExpenseSkladRepository $expenseSkladRepository;
    private IncomeStatusRepository $incomeStatusRepository;
    private ExpenseSkladDocumentFetcher $expenseSkladDocumentFetcher;
    private DocumentTypeRepository $documentTypeRepository;
    private ExpenseSkladDocumentRepository $expenseSkladDocumentRepository;

    public function __construct(
        ExpenseSkladRepository         $expenseSkladRepository,
        IncomeStatusRepository         $incomeStatusRepository,
        ExpenseSkladDocumentFetcher    $expenseSkladDocumentFetcher,
        ExpenseSkladDocumentRepository $expenseSkladDocumentRepository,
        DocumentTypeRepository         $documentTypeRepository,
        Flusher                        $flusher
    )
    {
        $this->flusher = $flusher;
        $this->expenseSkladRepository = $expenseSkladRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->expenseSkladDocumentFetcher = $expenseSkladDocumentFetcher;
        $this->documentTypeRepository = $documentTypeRepository;
        $this->expenseSkladDocumentRepository = $expenseSkladDocumentRepository;
    }

    public function handle(ZapSklad $zapSklad, Manager $manager): void
    {
        $firms = [];
        $expenses = $this->expenseSkladRepository->findAllPacked($zapSklad);
        foreach ($expenses as $expense) {
            if ($expense->getOrderGood()) {
                $orderGood = $expense->getOrderGood();
                $orderGood->shipBetweenSklads($expense);
                $orderGood->updateLastIncomeStatus($this->incomeStatusRepository->get(IncomeStatus::IN_PATH));
            } else {
                $expense->shipBetweenSklads();
            }

            $expense->send();

            if (!isset($firms[$expense->getIncome()->getFirm()->getId()])) {
                $document_num = $this->expenseSkladDocumentFetcher->getNextNP($expense->getIncome()->getFirm()->getId());
                $document = new Document($document_num);
                $expenseSkladDocument = new ExpenseSkladDocument(
                    $this->documentTypeRepository->get(DocumentType::NP),
                    $document,
                    $manager,
                    $expense->getZapSklad(),
                    $expense->getZapSkladTo(),
                    $expense->getIncome()->getFirm()
                );
                $this->expenseSkladDocumentRepository->add($expenseSkladDocument);
                $firms[$expense->getIncome()->getFirm()->getId()] = $expenseSkladDocument;
            }
            $expense->updateDocument($firms[$expense->getIncome()->getFirm()->getId()]);
        }

        $this->flusher->flush();
    }
}
