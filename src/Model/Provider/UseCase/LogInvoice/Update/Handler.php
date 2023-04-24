<?php

namespace App\Model\Provider\UseCase\LogInvoice\Update;

use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\LogInvoice\LogInvoice;
use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtdRepository;
use Doctrine\ORM\NonUniqueResultException;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private IncomeRepository $incomeRepository;
    private IncomeStatusRepository $incomeStatusRepository;
    private ShopGtdRepository $shopGtdRepository;
    private CountryRepository $countryRepository;

    public function __construct(
        IncomeRepository              $incomeRepository,
        IncomeStatusRepository        $incomeStatusRepository,
        ShopGtdRepository             $shopGtdRepository,
        CountryRepository             $countryRepository,
        Flusher                       $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeRepository = $incomeRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->shopGtdRepository = $shopGtdRepository;
        $this->countryRepository = $countryRepository;
    }

    public function handle(LogInvoice $logInvoice, Manager $manager): array
    {
        try {
            $income = $this->incomeRepository->findByLogInvoice($logInvoice);
            if (!$income) {
                throw new DomainException('Приход не найден');
            }

            $data = [
                ['value' => $income->getId(), 'name' => 'incomeID_' . $logInvoice->getId()],
                ['value' => '', 'name' => 'comment_' . $logInvoice->getId()],
                ['value' => number_format($income->getPriceZak(), 2, ',', ''), 'name' => 'priceZak_' . $logInvoice->getId()],
                ['value' => number_format($income->getPriceZak(), 2, ',', ''), 'name' => 'priceIncome_' . $logInvoice->getId()],
                ['value' => $income->getQuantity(), 'name' => 'quantityIncome_' . $logInvoice->getId()],
                ['value' => $income->getStatus()->getName(), 'name' => 'statusFrom_' . $logInvoice->getId()],
            ];

            if ($income->getPriceZak() != $logInvoice->getPriceInvoice()) {
                $addParentClasses = 'text-danger';
            } else {
                $addParentClasses = 'text-success';
            }

            $logInvoice->update($income);

            $status = $logInvoice->getStatusTo();
            $incomeStatus = $this->incomeStatusRepository->get($status);

            $incomeSklad = $income->getOneSkladOrCreate();

            if ($incomeStatus->isOnTheWayOrInIncomingOnWarehouse()) {
                $income->shipping($incomeSklad, $manager);
            } else if (
                $income->getStatus()->isOnTheWayOrInIncomingOnWarehouse() &&
                !$incomeStatus->isDeleted()
            ) {
                $income->returning($incomeSklad);
            }

            $income->updateStatus($incomeStatus, $manager);

            if ($logInvoice->getCountry() != '') {
                $country = $this->countryRepository->getByName($logInvoice->getCountry());
                if ($country && $income->getZapCard()->getManager() && $income->getZapCard()->getManager()->getId() != $country->getId()) {
                    $income->getZapCard()->updateCountry($country);
                }
            }

            if ($logInvoice->getGtd() != '') {
                $shopGtd = $this->shopGtdRepository->getOrCreate(new Gtd($logInvoice->getGtd()));
                $income->updateGtd($shopGtd);
            }
//
//
//            $logInvoice->updateCountry($income->getZapCard()->getCountry()->getName());
//            $data[] = ['value' => $income->getZapCard()->getCountry()->getName(), 'name' => 'country_' . $logInvoice->getId()];
//            if ($income->getShopGtd()) {
//                $logInvoice->updateGtd($income->getShopGtd()->getName()->getValue());
//                $data[] = ['value' => $income->getShopGtd()->getName()->getValue(), 'name' => 'gtd_' . $logInvoice->getId()];
//            } elseif ($logInvoice->getGtd() != '') {
//                $shopGtd = $this->shopGtdRepository->getOrCreate(new Gtd($logInvoice->getGtd()));
//                $income->updateGtd($shopGtd);
//            }

        } catch (DomainException | NonUniqueResultException $exception) {
            throw new DomainException($exception->getMessage());
        }

        $this->flusher->flush();

        return [
            'idIdentification' => $data,
            'addParentClasses' => [$addParentClasses]
        ];
    }

}
