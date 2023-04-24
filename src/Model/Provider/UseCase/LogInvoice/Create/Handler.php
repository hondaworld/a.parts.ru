<?php

namespace App\Model\Provider\UseCase\LogInvoice\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Provider\Entity\LogInvoice\LogInvoice;
use App\Model\Provider\Entity\LogInvoiceAll\LogInvoiceAll;
use App\Model\Provider\Entity\LogInvoiceAll\LogInvoiceAllRepository;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtdRepository;

class Handler
{
    private Flusher $flusher;
    private IncomeRepository $incomeRepository;
    private IncomeStatusRepository $incomeStatusRepository;
    private ShopGtdRepository $shopGtdRepository;
    private ManagerRepository $managerRepository;
    private LogInvoiceAllRepository $logInvoiceAllRepository;
    private CountryRepository $countryRepository;

    public function __construct(
        IncomeRepository              $incomeRepository,
        IncomeStatusRepository        $incomeStatusRepository,
        ShopGtdRepository             $shopGtdRepository,
        ManagerRepository             $managerRepository,
        LogInvoiceAllRepository       $logInvoiceAllRepository,
        CountryRepository             $countryRepository,
        Flusher                       $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeRepository = $incomeRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->shopGtdRepository = $shopGtdRepository;
        $this->managerRepository = $managerRepository;
        $this->logInvoiceAllRepository = $logInvoiceAllRepository;
        $this->countryRepository = $countryRepository;
    }

    public function handle(ProviderInvoice $providerInvoice, array $invoices): void
    {
        $manager = $this->managerRepository->getSuperAdmin();
        $incomes = $this->incomeRepository->findByProviderInvoice($providerInvoice);

        if ($invoices) {

            $logInvoiceAll = new LogInvoiceAll($providerInvoice);
            $this->logInvoiceAllRepository->add($logInvoiceAll);
            $isDone = true;

            foreach ($invoices as $invoice) {
                $logInvoice = new LogInvoice($providerInvoice, new DetailNumber($invoice['number']), $invoice['quantity'], $invoice['price']);

                $exists_number = false;
                $exists_quantity = false;
                foreach ($incomes as $k => $income) {

                    if ($invoice['number'] == $income->getZapCard()->getNumber()->getValue()) $exists_number = true;
                    if (($invoice['number'] == $income->getZapCard()->getNumber()->getValue()) && ($invoice["quantity"] == $income->getQuantity())) {
                        $exists_quantity = true;

                        $status = $providerInvoice->getStatusTo();
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

                        $logInvoice->update($income);

                        if ($invoice['country'] != '') {
                            $logInvoice->updateCountry($invoice['country']);

                            $country = $this->countryRepository->getByName($invoice['country']);
                            if ($country && $income->getZapCard()->getManager() && $income->getZapCard()->getManager()->getId() != $country->getId()) {
                                $income->getZapCard()->updateCountry($country);
                            }
                        }

                        if ($invoice['gtd'] != '') {
                            $logInvoice->updateGtd($invoice['gtd']);

                            $shopGtd = $this->shopGtdRepository->getOrCreate(new Gtd($invoice['gtd']));
                            $income->updateGtd($shopGtd);
                        }

                        if (($invoice["price"] != $income->getPriceZak()) && (($providerInvoice->getNum()->getPrice() != "") || ($providerInvoice->getNum()->getSumm() != ""))) {
                            $logInvoice->updateComment('Изменение цены');
                            $isDone = false;
                        }
                        unset($incomes[$k]);
                        break;
                    }
                }
                if (!$exists_number) {
                    $logInvoice->updateComment('Номера нет в приходах');
                    $isDone = false;
                } else if (!$exists_quantity) {
                    $logInvoice->updateComment('Количество не совпадает');
                    $isDone = false;
                }
                $logInvoiceAll->assignLog($logInvoice);
            }
            $logInvoiceAll->updateIsDone($isDone);
        }


        $this->flusher->flush();

    }
}
