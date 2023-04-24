<?php

namespace App\Model\Income\UseCase\Income\Status;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AlertType\OrderAlertTypeRepository;
use App\Model\Shop\Entity\DeleteReason\DeleteReasonRepository;
use DateTime;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private IncomeRepository $incomeRepository;
    private IncomeStatusRepository $incomeStatusRepository;
    private DeleteReasonRepository $deleteReasonRepository;
    private OrderAlertTypeRepository $orderAlertTypeRepository;

    public function __construct(
        IncomeRepository              $incomeRepository,
        IncomeStatusRepository        $incomeStatusRepository,
        DeleteReasonRepository        $deleteReasonRepository,
        OrderAlertTypeRepository      $orderAlertTypeRepository,
        Flusher                       $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeRepository = $incomeRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->deleteReasonRepository = $deleteReasonRepository;
        $this->orderAlertTypeRepository = $orderAlertTypeRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $dateofinplan = $command->dateofinplan ? new DateTime($command->dateofinplan) : null;

        $newStatus = $this->incomeStatusRepository->get($command->status);

        if ($newStatus->isOnTheWay() && !$command->dateofinplan) {
            throw new DomainException('Укажите, пожалуйста, планируемую дату прихода');
        }

        if ($newStatus->isFailByUser() && !$command->deleteReasonID) {
            throw new DomainException('Укажите, пожалуйста, причину отказа');
        }

        if ($newStatus->isInWarehouse()) {
            throw new DomainException('Оприходование на склад осуществляется через кнопку "Оприходование"');
        }

        if ($newStatus->isProcessed()) {
            throw new DomainException('Обработать приход можно через кнопку "Обработать приходы"');
        }

        $now = new DateTime();
        $incomes = [];

        foreach ($command->cols as $incomeID) {
            $income = $this->incomeRepository->get($incomeID);
            $incomes[] = $income;
            $message = $income->getStatus()->verifyNewStatus($income->getZapCard()->getNumber(), $newStatus);
            if ($message) $messages[] = ['type' => 'error', 'message' => $message];
        }

        if (empty($messages)) {
            foreach ($incomes as $income) {
                $income->updateDatesForChangeStatus($newStatus, $now, $dateofinplan);
                $incomeSklad = $income->getOneSkladOrCreate();

                if ($newStatus->isOnTheWayOrInIncomingOnWarehouse()) {
                    $income->shipping($incomeSklad, $manager);
                } else if (
                    $income->getStatus()->isOnTheWayOrInIncomingOnWarehouse() &&
                    !$newStatus->isDeleted()
                ) {
                    $income->returning($incomeSklad);
                }
                if ($newStatus->isDeleted()) {
                    $deleteReason = $command->deleteReasonID ? $this->deleteReasonRepository->get($command->deleteReasonID) : null;
                    $income->rejecting($incomeSklad, $deleteReason, $this->orderAlertTypeRepository->changeStatusType());
                }
                $income->updateStatus($newStatus, $manager);
            }
        }

        $this->flusher->flush();
        return $messages;
    }
}
