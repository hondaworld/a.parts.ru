<?php

namespace App\Model\Income\UseCase\Document\Create;

use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Document\Document;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Document\IncomeDocumentRepository;
use App\Model\Income\Entity\Document\Osn;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AlertType\OrderAlertTypeRepository;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\ReadModel\Income\IncomeDocumentFetcher;
use App\ReadModel\Income\IncomeFetcher;
use Doctrine\DBAL\Exception;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private IncomeStatusRepository $incomeStatusRepository;
    private IncomeFetcher $incomeFetcher;
    private IncomeRepository $incomeRepository;
    private ProviderRepository $providerRepository;
    private IncomeDocumentFetcher $incomeDocumentFetcher;
    private ContactRepository $contactRepository;
    private FirmRepository $firmRepository;
    private IncomeDocumentRepository $incomeDocumentRepository;
    private OrderAlertTypeRepository $orderAlertTypeRepository;
    private PriceGroupRepository $priceGroupRepository;
    private ZapSkladRepository $zapSkladRepository;
    private DocumentTypeRepository $documentTypeRepository;

    public function __construct(
        FirmRepository $firmRepository,
        ContactRepository $contactRepository,
        DocumentTypeRepository $documentTypeRepository,
        IncomeDocumentFetcher $incomeDocumentFetcher,
        IncomeDocumentRepository $incomeDocumentRepository,
        IncomeFetcher $incomeFetcher,
        IncomeRepository $incomeRepository,
        ProviderRepository $providerRepository,
        IncomeStatusRepository $incomeStatusRepository,
        OrderAlertTypeRepository $orderAlertTypeRepository,
        PriceGroupRepository $priceGroupRepository,
        ZapSkladRepository $zapSkladRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->incomeFetcher = $incomeFetcher;
        $this->incomeRepository = $incomeRepository;
        $this->providerRepository = $providerRepository;
        $this->incomeDocumentFetcher = $incomeDocumentFetcher;
        $this->contactRepository = $contactRepository;
        $this->firmRepository = $firmRepository;
        $this->incomeDocumentRepository = $incomeDocumentRepository;
        $this->orderAlertTypeRepository = $orderAlertTypeRepository;
        $this->priceGroupRepository = $priceGroupRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->documentTypeRepository = $documentTypeRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        try {
            if ($this->incomeFetcher->isExistNotPriceZakIncomeInWarehouse($command->providerID)) {
                throw new DomainException('Есть детали с нулевой ценой. Оприходовать невозможно.');
            }

            if ($this->incomeFetcher->isExistUnPackIncomeInWarehouse($command->providerID)) {
                throw new DomainException('Есть не посчитанные детали. Оприходовать невозможно.');
            }
        } catch (Exception $e) {
            throw new DomainException($e->getMessage());
        }

        $provider = $this->providerRepository->get($command->providerID);
        $incomes = $this->incomeRepository->findByProviderIncomeInWarehouse($provider);

        if ($incomes) {

            $incomeStatus = $this->incomeStatusRepository->get(IncomeStatus::IN_WAREHOUSE);

            $firm = $this->firmRepository->get($command->firmID);

            try {
                $document_num = $this->incomeDocumentFetcher->getNextPN($command->firmID);
            } catch (Exception $e) {
                throw new DomainException($e->getMessage());
            }

            $document = new Document($document_num, $command->document_prefix, $command->document_sufix);
            $osn = new Osn('', $command->osn_nakladnaya, $command->osn_schet);
            $incomeDocument = new IncomeDocument($this->documentTypeRepository->get(DocumentType::PN), $document, $manager, $provider, $provider->getUser(), $this->contactRepository->get($command->user_contactID), $firm, $osn);
            $this->incomeDocumentRepository->add($incomeDocument);

            $sum = 0;
            foreach ($incomes as $income) {

                $income->updateStatus($incomeStatus, $manager);
                $incomeSklad = $income->getOneSkladOrCreate();
                $income->incomeInWarehouse($incomeDocument, $firm);
                $incomeSklad->incomeInWarehouse();

                $sum += $income->getSum();

                if ($income->isOrderIncome()) {
                    $orderGood = $income->getFirstOrderGood();
                    $orderGood->assignAlert($this->orderAlertTypeRepository->changeStatusType());
                } else {
                    if ($command->is_priceZak) {
                        $income->changeZapCardPrice();
                        if (!$income->getZapCard()->isPriceGroupFix()) {
                            $income->getZapCard()->updatePriceGroup($this->priceGroupRepository->getForIncomeInWarehouse($income->getProviderPrice()), $income->getZapCard()->isPriceGroupFix());
                        }
                    }
                }

                foreach ($this->zapSkladRepository->findAllNotHide() as $zapSklad) {
                    $income->getZapCard()->assignLocation($zapSklad);
                }
                $this->flusher->flush();
            }

            $firm->assignFirmBalanceHistory($provider, $sum, $firm->getNDS($sum), $manager, '', $incomeDocument);

            $command->balance = $command->normalizeNumber($command->balance);
            $command->balance_nds = $command->normalizeNumber($command->balance_nds);

            if ($command->balance && (is_numeric($command->balance)) && ($command->balance > 0)) {
                $firm->assignFirmBalanceHistory($provider, -$command->balance, -$command->balance_nds, $manager, $command->description, null);
            }
        }

        $this->flusher->flush();
        return $messages;
    }
}
