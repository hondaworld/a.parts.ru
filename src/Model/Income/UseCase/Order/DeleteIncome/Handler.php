<?php

namespace App\Model\Income\UseCase\Order\DeleteIncome;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Income\Entity\Order\IncomeOrderRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Income\Entity\StatusHistory\IncomeStatusHistory;
use App\Model\Income\Entity\StatusHistory\IncomeStatusHistoryRepository;
use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\ORM\EntityManagerInterface;

class Handler
{
    private Flusher $flusher;
    private IncomeStatusRepository $incomeStatusRepository;
    private IncomeStatusHistoryRepository $incomeStatusHistoryRepository;
    private IncomeOrderRepository $incomeOrderRepository;

    public function __construct(
        IncomeStatusRepository $incomeStatusRepository,
        IncomeStatusHistoryRepository $incomeStatusHistoryRepository,
        IncomeOrderRepository $incomeOrderRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->incomeStatusHistoryRepository = $incomeStatusHistoryRepository;
        $this->incomeOrderRepository = $incomeOrderRepository;
    }

    public function handle(IncomeOrder $incomeOrder, Income $income, Manager $manager, EntityManagerInterface $em, bool $isDeleteIncome = false): bool
    {
        $isRedirect = false;

        $incomeStatus = $this->incomeStatusRepository->get(IncomeStatus::DEFAULT_STATUS);
        $income->updateStatus($incomeStatus, $manager);
        $incomeOrder->removeIncome($income);
        $income->clearSklads();

        if ($isDeleteIncome) {
            $income->clearOrderGoods();
            $em->remove($income);
        }

        if (count($incomeOrder->getIncomes()) == 0) {
            if ($this->incomeOrderRepository->hasNextDocumentNum($incomeOrder)) {
                $incomeOrder->deleted();
            } else {
                $em->remove($incomeOrder);
            }
            $isRedirect = true;
        }

        $this->flusher->flush();
        return $isRedirect;
    }
}
