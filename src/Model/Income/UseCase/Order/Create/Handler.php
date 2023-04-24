<?php

namespace App\Model\Income\UseCase\Order\Create;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Income\Entity\Order\IncomeOrderRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\ReadModel\Income\IncomeOrderFetcher;
use Doctrine\DBAL\Exception;

class Handler
{
    private Flusher $flusher;
    private IncomeRepository $incomeRepository;
    private ZapSkladRepository $zapSkladRepository;
    private IncomeOrderRepository $incomeOrderRepository;
    private IncomeOrderFetcher $incomeOrderFetcher;
    private IncomeStatusRepository $incomeStatusRepository;

    public function __construct(
        IncomeRepository $incomeRepository,
        IncomeOrderRepository $incomeOrderRepository,
        IncomeOrderFetcher $incomeOrderFetcher,
        IncomeStatusRepository $incomeStatusRepository,
        ZapSkladRepository $zapSkladRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeRepository = $incomeRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->incomeOrderRepository = $incomeOrderRepository;
        $this->incomeOrderFetcher = $incomeOrderFetcher;
        $this->incomeStatusRepository = $incomeStatusRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $zapSklad = $this->zapSkladRepository->get($command->zapSkladID);
        $incomeStatusInWork = $this->incomeStatusRepository->get(IncomeStatus::IN_WORK);
//        $arr = [];
        $incomeOrders = [];
        foreach ($command->cols as $incomeID) {
            $income = $this->incomeRepository->get($incomeID);
            $provider = $income->getProviderPrice()->getProvider();

            if (!$income->getStatus()->isNew()) {
                $messages[] = [
                    'type' => 'error',
                    'message' => 'Деталь ' . $income->getZapCard()->getNumber()->getValue() . ' не в статусе "Заказать"'
                ];
            }

            if ($income->getStatus()->isNew() && $provider->isIncomeOrder()) {
                if (isset($incomeOrders[$provider->getId()])) {
                    $incomeOrder = $incomeOrders[$provider->getId()];
                } else {
                    $incomeOrder = $this->getIncomeOrder($provider, $zapSklad);
                    $incomeOrders[$provider->getId()] = $incomeOrder;
                }

                $income->updateIncomeOrder($incomeOrder);
                $income->updateStatus($incomeStatusInWork, $manager);
                $income->getOneSkladOrCreate($zapSklad);
            }
        }

        $this->flusher->flush();

        return $messages;
    }

    /**
     * @param Provider $provider
     * @param ZapSklad $zapSklad
     * @return IncomeOrder
     */
    private function getIncomeOrder(Provider $provider, ZapSklad $zapSklad): IncomeOrder
    {
        $incomeOrder = $this->incomeOrderRepository->getNotOrderedByProviderAndZapSklad($provider, $zapSklad);
        if (!$incomeOrder) {
            try {
                $document_num = $this->incomeOrderFetcher->getNextDocumentNumber($provider);
            } catch (Exception $e) {
                throw new \DomainException($e->getMessage());
            }

            $incomeOrder = $provider->assignIncomeOrderAndReturn($zapSklad, $document_num);
        }
        return $incomeOrder;
    }
}
