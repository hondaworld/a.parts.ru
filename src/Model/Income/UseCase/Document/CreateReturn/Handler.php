<?php

namespace App\Model\Income\UseCase\Document\CreateReturn;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Document\Document;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Document\IncomeDocumentRepository;
use App\Model\Income\Entity\Document\Osn;
use App\Model\Income\Entity\Sklad\IncomeSkladRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\ReadModel\Income\IncomeDocumentFetcher;
use Doctrine\DBAL\Exception;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private ProviderRepository $providerRepository;
    private IncomeDocumentFetcher $incomeDocumentFetcher;
    private FirmRepository $firmRepository;
    private IncomeDocumentRepository $incomeDocumentRepository;
    private IncomeSkladRepository $incomeSkladRepository;
    private DocumentTypeRepository $documentTypeRepository;

    public function __construct(
        FirmRepository $firmRepository,
        ProviderRepository $providerRepository,
        DocumentTypeRepository $documentTypeRepository,
        IncomeDocumentFetcher $incomeDocumentFetcher,
        IncomeDocumentRepository $incomeDocumentRepository,
        IncomeSkladRepository $incomeSkladRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeDocumentFetcher = $incomeDocumentFetcher;
        $this->firmRepository = $firmRepository;
        $this->incomeDocumentRepository = $incomeDocumentRepository;
        $this->incomeSkladRepository = $incomeSkladRepository;
        $this->documentTypeRepository = $documentTypeRepository;
        $this->providerRepository = $providerRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $isReturn = false;
        foreach ($command->incomeSklads as $value) {
            if (intval($value) != 0) $isReturn = true;
        }

        if (!$isReturn) {
            throw new DomainException('Не указано количество для возврата');
        }

        $provider = $this->providerRepository->get($command->providerID);


        $firm = $this->firmRepository->get($command->firmID);
        try {
            $document_num = $this->incomeDocumentFetcher->getNextVN($command->firmID);
        } catch (Exception $e) {
            throw new DomainException($e->getMessage());
        }
        $document = new Document($document_num, $command->document_prefix, $command->document_sufix);
        $osn = new Osn();

        $incomeDocument = new IncomeDocument($this->documentTypeRepository->get(DocumentType::VN), $document, $manager, $provider, $provider->getUser(), null, $firm, $osn);
        $this->incomeDocumentRepository->add($incomeDocument);

        $sum = 0;
        foreach ($command->incomeSklads as $incomeSkladID => $quantity) {

            if ($quantity > 0) {
                $incomeSklad = $this->incomeSkladRepository->get($incomeSkladID);

                if ($incomeSklad->getQuantityIn() - $incomeSklad->getReserve() < $quantity) {
                    throw new DomainException('Возвращаемое количество больше доступного');
                }

                $income = $incomeSklad->getIncome();

                $income->returnQuantity($quantity);
                $incomeSklad->returnQuantity($quantity);

                $incomeDocument->assignIncomeGood($income, $incomeSklad, $manager, $quantity, $command->returning_reason);

                $sum += -$income->getPrice() * $quantity;
            }
        }

        $firm->assignFirmBalanceHistory($provider, $sum, 0, $manager, '', $incomeDocument);

        $this->flusher->flush();
        return $messages;
    }
}
