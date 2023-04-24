<?php

namespace App\Model\Provider\UseCase\LogInvoice\UpdatePrices;

use App\Model\Flusher;
use App\Model\Provider\Entity\LogInvoice\LogInvoiceRepository;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private LogInvoiceRepository $logInvoiceRepository;
    private PartPriceService $partPriceService;

    public function __construct(
        LogInvoiceRepository $invoiceRepository,
        PartPriceService     $partPriceService,
        Flusher              $flusher
    )
    {
        $this->flusher = $flusher;
        $this->logInvoiceRepository = $invoiceRepository;
        $this->partPriceService = $partPriceService;
    }

    public function handle(Command $command): array
    {
        $messages = [];

        foreach ($command->cols as $logID) {
            $logInvoice = $this->logInvoiceRepository->get($logID);


            try {
                if (!$logInvoice->getIncome()) {
                    throw new DomainException("Деталь " . $logInvoice->getNumber()->getValue() . " не имеет прихода");
                }

                if (!$logInvoice->getPriceInvoice()) {
                    throw new DomainException("Деталь " . $logInvoice->getNumber()->getValue() . " не имеет цены");
                }

                $income = $logInvoice->getIncome();

                $prices = $this->partPriceService->onePriceWithPriceZak($income->getZapCard()->getNumber(), $income->getZapCard()->getCreater(), $income->getProviderPrice(), $logInvoice->getPriceInvoice());
                if ($prices['priceZak'] != 0) {
                    $income->updatePrices($prices['priceZak'], $prices['priceDostUsd'], $prices['priceWithDostRub']);
                }

            } catch (DomainException | Exception $exception) {
                $messages[] = ['type' => 'danger', 'message' => $exception->getMessage()];
            }
        }

        $this->flusher->flush();

        return $messages;
    }
}
