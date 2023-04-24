<?php

namespace App\Model\Order\UseCase\Good\CreateFile;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\User\Entity\User\User;
use App\ReadModel\Detail\PartPriceFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\Service\CsvUploadHelper;
use App\Service\Detail\CreaterService;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class HandlerData
{
    private CreaterRepository $createrRepository;
    private CsvUploadHelper $csvUploadHelper;
    private CreaterService $createrService;
    private ProviderPriceRepository $providerPriceRepository;
    private PartPriceService $partPriceService;
    private ZapCardRepository $zapCardRepository;
    private ZapCardPriceService $zapCardPriceService;
    private IncomeFetcher $incomeFetcher;
    private PartPriceFetcher $partPriceFetcher;

    public function __construct(
        CreaterRepository       $createrRepository,
        CsvUploadHelper         $csvUploadHelper,
        CreaterService          $createrService,
        ProviderPriceRepository $providerPriceRepository,
        PartPriceService        $partPriceService,
        PartPriceFetcher        $partPriceFetcher,
        ZapCardRepository       $zapCardRepository,
        ZapCardPriceService     $zapCardPriceService,
        IncomeFetcher           $incomeFetcher
    )
    {
        $this->createrRepository = $createrRepository;
        $this->csvUploadHelper = $csvUploadHelper;
        $this->createrService = $createrService;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->partPriceService = $partPriceService;
        $this->zapCardRepository = $zapCardRepository;
        $this->zapCardPriceService = $zapCardPriceService;
        $this->incomeFetcher = $incomeFetcher;
        $this->partPriceFetcher = $partPriceFetcher;
    }

    public function handle(Command $command, User $user, UploadedFile $file): array
    {
        $arr = [];
        if (!$command->createrID && $command->creater_num == null) {
            throw new DomainException('Выберите производителя или поле с производителем');
        }

        $creater = null;
        if ($command->createrID) {
            $creater = $this->createrRepository->get($command->createrID);
        }

        $DataFile = fopen($file->getPathname(), "r");
        $i = 1;
        while (!feof($DataFile)) {

            $line = $this->csvUploadHelper->getCsvLine($DataFile);
            if ($command->first_line && $i == 1) {
                $line = $this->csvUploadHelper->getCsvLine($DataFile);
            }

            try {
                if ($line && isset($line[$command->number_num])) {
                    $number = new DetailNumber($this->csvUploadHelper->convertText(trim($line[$command->number_num])));
                    if (!$command->createrID && $command->creater_num != null && isset($line[$command->creater_num])) {
                        $createrID = $this->createrService->findCreaterIDFromCsv($line[$command->creater_num]);
                        if ($createrID) {
                            $creater = $this->createrRepository->get($createrID);
                        } else {
                            $creater = null;
                        }
                    }

                    if ($number->getValue() != '') {
                        $quantity = isset($line[$command->quantity_num]) ? intval($line[$command->quantity_num]) : 0;
                        if ($command->price_num != null && isset($line[$command->price_num])) {
                            $price = floatval(str_replace(',', '.', $line[$command->price_num]));
                        }
                        $discount = 0;
                        $price1 = 0;
                        $quantityInWarehouse = 0;
                        $isAdd = true;
                        if ($creater) {
                            if ($command->providerPriceID) {
                                $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);
                                $price1 = $this->partPriceService->onePriceClient($number, $creater, $providerPrice, $user->getOpt());
                                if ($price1 == 0) {
                                    $isAdd = false;
                                } else {
                                    $stock = $this->partPriceService->getPriceStock($number, $creater, $providerPrice);
                                    if (!$stock->hasPrice()) {
                                        $discount = $providerPrice->getDiscountParts($user->getDiscountParts());
                                    }
                                    $quantityInWarehouse = $this->partPriceFetcher->quantityInPrice($number, $creater, $providerPrice);
                                }
                            } elseif ($command->zapSkladID) {
                                try {
                                    $zapCard = $this->zapCardRepository->getByNumberAndCreater($number, $creater);
                                    $price1 = $this->zapCardPriceService->priceOpt($zapCard, $user->getOpt());
                                    $stock = $this->partPriceService->getPriceStock($number, $creater);
                                    if (!$stock->hasPrice()) {
                                        $discount = $user->getDiscountParts();
                                    }
                                    $arrQuantityInWarehouse = $this->incomeFetcher->findQuantityInWarehouseByZapCard($zapCard->getId());
                                    foreach ($arrQuantityInWarehouse as $zapSkladID => $q) {
                                        if ($zapSkladID == $command->zapSkladID) {
                                            $quantityInWarehouse = $q;
                                        }
                                    }
                                } catch (DomainException $e) {
                                    $isAdd = false;
                                }
                            }
                        } else {
                            $isAdd = false;
                        }
                        $arr[] = [
                            'number' => $number->getValue(),
                            'createrID' => $isAdd ? $creater->getId() : 0,
                            'zapCardID' => $isAdd && !$command->providerPriceID ? $zapCard->getId() : 0,
                            'creater_name' => $creater ? $creater->getName() : ($command->creater_num != null ? $line[$command->creater_num] : 'Производитель не найден'),
                            'quantity' => $quantity,
                            'quantityInWarehouse' => $quantityInWarehouse,
                            'arrQuantityInWarehouse' => $arrQuantityInWarehouse ?? [],
                            'price' => $price ?? 0,
                            'price1' => round($price1 - $price1 * $discount / 100),
                            'discount' => $discount,
                            'isAdd' => $isAdd
                        ];
                    }
                }
            } catch (Exception $e) {
                throw new DomainException($e->getMessage());
            }
            $i++;
        }

//        $this->flusher->flush();
//        dump($arr);
        return $arr;
    }
}
