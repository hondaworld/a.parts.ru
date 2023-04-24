<?php

namespace App\Model\Work\Service;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Period\WorkPeriod;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\Service\Price\PartPriceService;

class WorkPeriodService
{
    private PartPriceService $partPriceService;
    private OptRepository $optRepository;
    private array $providerPrices;
    private ZapCardRepository $zapCardRepository;

    public function __construct(PartPriceService $partPriceService, OptRepository $optRepository, ProviderPriceFetcher $providerPriceFetcher, ZapCardRepository $zapCardRepository)
    {
        $this->partPriceService = $partPriceService;
        $this->optRepository = $optRepository;
        $this->providerPrices = $providerPriceFetcher->allArray();
        $this->zapCardRepository = $zapCardRepository;
    }

    public function get(WorkPeriod $workPeriod): array
    {
        return $this->getWorkGroups($workPeriod);
    }

    private function getWorkGroups(WorkPeriod $workPeriod): array
    {
        $works = [
            'main' => [],
            'dop' => [],
            'rec' => []
        ];

        foreach ($workPeriod->getGroups() as $group) {
            foreach ($group->getAutos() as $auto) {
                if ($auto->isEqual($workPeriod->getAutoModification())) {
                    $works['main'][$group->getId()] = [
                        'id' => $group->getId(),
                        'name' => $group->getName(),
                        'isTO' => $group->getIsTO(),
                        'norma' => $this->getWorkNorma($workPeriod, $group),
                        'parts' => $this->getWorkParts($workPeriod, $group),
                    ];
                }
            }
        }

        foreach ($workPeriod->getGroupsDop() as $group) {
            foreach ($group->getAutos() as $auto) {
                if ($auto->isEqual($workPeriod->getAutoModification()) && !isset($works['main'][$group->getId()])) {
                    $works['dop'][$group->getId()] = [
                        'id' => $group->getId(),
                        'name' => $group->getName(),
                        'isTO' => $group->getIsTO(),
                        'norma' => $this->getWorkNorma($workPeriod, $group),
                        'parts' => $this->getWorkParts($workPeriod, $group),
                    ];
                }
            }
        }

        foreach ($workPeriod->getGroupsRec() as $group) {
            foreach ($group->getAutos() as $auto) {
                if ($auto->isEqual($workPeriod->getAutoModification()) && !isset($works['main'][$group->getId()]) && !isset($works['dop'][$group->getId()])) {
                    $works['rec'][$group->getId()] = [
                        'id' => $group->getId(),
                        'name' => $group->getName(),
                        'isTO' => $group->getIsTO(),
                        'norma' => $this->getWorkNorma($workPeriod, $group),
                        'parts' => $this->getWorkParts($workPeriod, $group),
                    ];
                }
            }
        }
        return $works;
    }

    private function getWorkNorma(WorkPeriod $workPeriod, WorkGroup $workGroup): float
    {
        $norma = $workGroup->getNorma();
        $autoModification = $workPeriod->getAutoModification();
        foreach ($workGroup->getAutosNorma() as $auto) {
            if ($auto->isEqualMarka($autoModification)) {
                $norma = $auto->getNorma();
            }
        }
        foreach ($workGroup->getAutosNorma() as $auto) {
            if ($auto->isEqualModel($autoModification)) {
                $norma = $auto->getNorma();
            }
        }
        foreach ($workGroup->getAutosNorma() as $auto) {
            if ($auto->isEqualGeneration($autoModification)) {
                $norma = $auto->getNorma();
            }
        }
        foreach ($workGroup->getAutosNorma() as $auto) {
            if ($auto->isEqualModification($autoModification)) {
                $norma = $auto->getNorma();
            }
        }
        return $norma;
    }

    private function getWorkParts(WorkPeriod $workPeriod, WorkGroup $workGroup): array
    {
        $parts = [];
        $autoModification = $workPeriod->getAutoModification();
        foreach ($workGroup->getAutosParts() as $auto) {
            if ($auto->isEqualMarka($autoModification)) {
                $parts = $this->getPrices($this->convertParts($auto->getParts()));
            }
        }
        foreach ($workGroup->getAutosParts() as $auto) {
            if ($auto->isEqualModel($autoModification)) {
                $parts = $this->getPrices($this->convertParts($auto->getParts()));
            }
        }
        foreach ($workGroup->getAutosParts() as $auto) {
            if ($auto->isEqualGeneration($autoModification)) {
                $parts = $this->getPrices($this->convertParts($auto->getParts()));
            }
        }
        foreach ($workGroup->getAutosParts() as $auto) {
            if ($auto->isEqualModification($autoModification)) {
                $parts = $this->getPrices($this->convertParts($auto->getParts()));
            }
        }
        return $parts;
    }

    private function convertParts(string $parts): array
    {
        $arParts = [];
        if ($parts != '') {
            $arPartNumbers = explode("\n", $parts);
            if ($arPartNumbers) {
                foreach ($arPartNumbers as $arPartNumber) {
                    $arPartNumberQuantities = explode(";", $arPartNumber);
                    if (count($arPartNumberQuantities) > 1) {
                        $arParts[] = array(
                            'number' => $arPartNumberQuantities[0],
                            'quantity' => intval($arPartNumberQuantities[1] ?? 1)
                        );
                    } else {
                        $arParts[] = array(
                            'number' => $arPartNumberQuantities[0],
                            'quantity' => 1
                        );
                    }
                }
            }
        }
        return $arParts;
    }

    /**
     * @param array $numbers
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPrices(array $numbers): array
    {
        $providerPrices = $this->providerPrices;

        $zapCards = $this->zapCardRepository->findByNumbers(
            array_map(function ($item) {
                return $item['number'];
            }, $numbers)
        );

        $parts = [];
        foreach ($numbers as $item) {
            $number = $item['number'];
            if (isset($zapCards[$number])) {
                $arParts = $this->partPriceService->fullPriceForKit(new DetailNumber($number), $this->optRepository->get(Opt::DEFAULT_OPT_ID));

                foreach ($arParts as &$part) {
                    if (isset($part['zapSkladID'])) {
                        $part['sklad'] = 1;
                    } elseif ($providerPrices[$part['providerPriceID']]['providerPriceGroupID'] == 2)
                        $part["sklad"] = 2;
                    else
                        $part["sklad"] = 3;
                }

                $arParts = array_values(array_filter($arParts, function ($part) use ($item) {
                    return $part['quantity'] >= $item['quantity'] || $part['sklad'] == 3;
                }));

                usort($arParts, function ($a, $b) use ($providerPrices) {
                    if ($a['isZamena'] != $b['isZamena']) return $a['isZamena'] <=> $b['isZamena'];
                    if ($a['isOriginal'] != $b['isOriginal']) return $a['isOriginal'] <=> $b['isOriginal'];
                    if ($a['sklad'] != $b['sklad']) return $a['sklad'] <=> $b['sklad'];
                    if ($a['number'] == $b['number'] && isset($a['zapSkladID']) && isset($b['zapSkladID']) && $a['zapSkladID'] != $b['zapSkladID']) return $a['zapSkladID'] <=> $b['zapSkladID'];
                    return $a['price1'] <=> $b['price1'];
                });


                $parts[] = [
                    'number' => $number,
                    'creater' => $zapCards[$number] ? $zapCards[$number]->getCreater()->getName() : '',
                    'name' => $zapCards[$number] ? $zapCards[$number]->getDetailName() : '',
                    'quantity' => $item['quantity'],
                    'parts' => $arParts
                ];
            }
        }
        return $parts;
    }
}