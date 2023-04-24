<?php

namespace App\Model\Order\UseCase\Document\CreateReturn;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Document\Document;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Document\IncomeDocumentRepository;
use App\Model\Income\Entity\Document\Osn;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Sklad\IncomeSklad;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\ReadModel\Income\IncomeDocumentFetcher;
use DateTime;
use Doctrine\DBAL\Exception;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private IncomeDocumentFetcher $incomeDocumentFetcher;
    private IncomeDocumentRepository $incomeDocumentRepository;
    private DocumentTypeRepository $documentTypeRepository;
    private OrderGoodRepository $orderGoodRepository;
    private ExpenseDocumentRepository $expenseDocumentRepository;
    private ZapCardRepository $zapCardRepository;
    private ZapSkladRepository $zapSkladRepository;
    private IncomeRepository $incomeRepository;
    private IncomeStatusRepository $incomeStatusRepository;

    public function __construct(
        DocumentTypeRepository       $documentTypeRepository,
        IncomeDocumentFetcher        $incomeDocumentFetcher,
        IncomeDocumentRepository     $incomeDocumentRepository,
        OrderGoodRepository          $orderGoodRepository,
        ExpenseDocumentRepository    $expenseDocumentRepository,
        ZapCardRepository            $zapCardRepository,
        ZapSkladRepository           $zapSkladRepository,
        IncomeRepository             $incomeRepository,
        IncomeStatusRepository       $incomeStatusRepository,
        Flusher                      $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeDocumentFetcher = $incomeDocumentFetcher;
        $this->incomeDocumentRepository = $incomeDocumentRepository;
        $this->documentTypeRepository = $documentTypeRepository;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->incomeRepository = $incomeRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $isReturn = false;
        foreach ($command->goods as $value) {
            if (intval($value) != 0) $isReturn = true;
        }

        if (!$isReturn) {
            throw new DomainException('Не указано количество для возврата');
        }

        $expenseDocumentID = 0;
        foreach ($command->goods as $goodID => $quantity) {
            if ($quantity > 0) {
                $orderGood = $this->orderGoodRepository->get($goodID);
                if (!$orderGood->getExpenseDocument()) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " не отгружена");
                }

                if ($expenseDocumentID == 0) $expenseDocumentID = $orderGood->getExpenseDocument()->getId();
                if ($expenseDocumentID != $orderGood->getExpenseDocument()->getId()) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " от другой отгрузки");
                }

                if ($orderGood->getQuantity() == $orderGood->getQuantityReturn()) {
                    throw new DomainException("Все количество детали " . $orderGood->getNumber()->getValue() . " уже возвращено");
                }
            }
        }

        $zapSklad = $this->zapSkladRepository->get($command->zapSkladD);

        $expenseDocument = $this->expenseDocumentRepository->get($expenseDocumentID);
        try {
            $document_num = $this->incomeDocumentFetcher->getNextVZ($expenseDocument->getFirm()->getId());
        } catch (Exception $e) {
            throw new DomainException($e->getMessage());
        }

        $document = new Document($document_num, $command->document_prefix, $command->document_sufix);
        $osn = new Osn();
        $incomeDocument = new IncomeDocument($this->documentTypeRepository->get(DocumentType::VZ), $document, $manager, null, $expenseDocument->getExpUser(), null, $expenseDocument->getFirm(), $osn);
        $this->incomeDocumentRepository->add($incomeDocument);


        foreach ($command->goods as $goodID => $quantity) {
            if ($quantity > 0) {
                $orderGood = $this->orderGoodRepository->get($goodID);
                if ($quantity > $orderGood->getQuantity() - $orderGood->getQuantityReturn()) $quantity = $orderGood->getQuantity() - $orderGood->getQuantityReturn();

                $zapCard = $this->zapCardRepository->getOrCreate($orderGood->getNumber(), $orderGood->getCreater());
                $zapCard->assignLocation($zapSklad);
//                $this->zapSkladLocationRepository->getOrCreate($zapCard, $zapSklad);

                $income = new Income(null, $this->incomeStatusRepository->get(IncomeStatus::IN_WAREHOUSE), $zapCard, $quantity, $orderGood->getDiscountPrice(), 0, $orderGood->getDiscountPrice());
                $income->incomeInWarehouse($incomeDocument, $expenseDocument->getFirm());
                $this->incomeRepository->add($income);

                $incomeSklad = new IncomeSklad($income, $zapSklad, $quantity);
                $incomeSklad->incomeInWarehouse();
                $income->assignSklad($incomeSklad);
//                $this->incomeSkladRepository->add($incomeSklad);

                $orderGood->increaseQuantityReturn($quantity);

                $orderGoodReturn = OrderGood::cloneFromReturn($orderGood, $incomeDocument, $zapSklad, $quantity, $manager, $orderGood->getDiscountPrice(), $command->returning_reason ?: '');
                $this->orderGoodRepository->add($orderGoodReturn);

                $user = $orderGood->getOrder()->getUser();
                $user->credit($orderGood->getDiscountPrice() * $quantity, $expenseDocument->getFinanceType(), $manager, $expenseDocument->getFirm(), "Возврат детали по документу " . $document_num . " от " . (new DateTime())->format('d.m.Y') . " по причине " . $command->returning_reason ?: '');

                $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Возврат детали", $orderGood->getNumber()->getValue());

                $this->flusher->flush();
            }
        }

        $this->flusher->flush();
        return $messages;
    }
}
