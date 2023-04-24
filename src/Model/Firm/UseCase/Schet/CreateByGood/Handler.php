<?php

namespace App\Model\Firm\UseCase\Schet\CreateByGood;

use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Finance\Entity\FinanceType\FinanceTypeRepository;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;
use App\Model\Order\Entity\Good\OrderGood;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;
    private FinanceTypeRepository $financeTypeRepository;

    public function __construct(
        SchetRepository $schetRepository,
        FinanceTypeRepository $financeTypeRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
        $this->financeTypeRepository = $financeTypeRepository;
    }

    public function handle(OrderGood $good): void
    {
        if ($good->getSchet()) {
            $good->removeSchet();
        } else {
            $schet = $this->schetRepository->findNewByUser($good->getOrder()->getUser());
            if (!$schet) {
                if ($good->getExpenseDocument() && $good->getExpenseDocument()->getExpFirm()) {
                    $firm = $good->getExpenseDocument()->getExpFirm();
                }
                $schet = new Schet($this->financeTypeRepository->get(FinanceType::DEFAULT_BEZNAL_ID), $good->getOrder()->getUser(), $firm ?? null);
                $this->schetRepository->add($schet);
            }
            $good->updateSchet($schet);
        }

        $this->flusher->flush();
    }
}
