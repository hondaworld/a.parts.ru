<?php

namespace App\Model\Work\UseCase\Group\Auto;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Link\LinkWorkAuto;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $workGroupID;

    /**
     * @var AutoMarka
     */
    public $autoMarka;

    public $linkMarka;

    public $normaMarka;

    public $partsMarka;

    /**
     * @var array
     */
    public $linkModel = [];

    /**
     * @var array
     */
    public $normaModel = [];

    /**
     * @var array
     */
    public $partsModel = [];

    /**
     * @var array
     */
    public $linkGeneration = [];

    /**
     * @var array
     */
    public $normaGeneration = [];

    /**
     * @var array
     */
    public $partsGeneration = [];

    /**
     * @var array
     */
    public $linkModification = [];

    /**
     * @var array
     */
    public $normaModification = [];

    /**
     * @var array
     */
    public $partsModification = [];

    public function __construct(int $workGroupID)
    {
        $this->workGroupID = $workGroupID;
    }

    public static function fromEntity(WorkGroup $workGroup, AutoMarka $autoMarka, array $linkWorkAuto, array $linkWorkNormaAuto, array $linkWorkPartsAuto): self
    {
        $command = new self($workGroup->getId());
        $command->autoMarka = $autoMarka;
        $command->linkMarka = $autoMarka->getWorkAutosByWorkGroup($linkWorkAuto, $workGroup);
        $command->normaMarka = $autoMarka->getWorkAutosNormaByWorkGroup($linkWorkNormaAuto, $workGroup);
        $command->partsMarka = $autoMarka->getWorkAutosPartsByWorkGroup($linkWorkPartsAuto, $workGroup);
        foreach ($autoMarka->getModels() as $model) {
            $command->linkModel += [
                $model->getId() => $model->getWorkAutosByWorkGroup($linkWorkAuto, $workGroup)
            ];
            $command->normaModel += [
                $model->getId() => $model->getWorkAutosNormaByWorkGroup($linkWorkNormaAuto, $workGroup)
            ];
            $command->partsModel += [
                $model->getId() => $model->getWorkAutosPartsByWorkGroup($linkWorkPartsAuto, $workGroup)
            ];
            foreach ($model->getGenerations() as $generation) {
                $command->linkGeneration += [
                    $generation->getId() => $generation->getWorkAutosByWorkGroup($linkWorkAuto, $workGroup)
                ];
                $command->normaGeneration += [
                    $generation->getId() => $generation->getWorkAutosNormaByWorkGroup($linkWorkNormaAuto, $workGroup)
                ];
                $command->partsGeneration += [
                    $generation->getId() => $generation->getWorkAutosPartsByWorkGroup($linkWorkPartsAuto, $workGroup)
                ];
                foreach ($generation->getModifications() as $modification) {
                    $command->linkModification += [
                        $modification->getId() => $modification->getWorkAutosByWorkGroup($linkWorkAuto, $workGroup)
                    ];
                    $command->normaModification += [
                        $modification->getId() => $modification->getWorkAutosNormaByWorkGroup($linkWorkNormaAuto, $workGroup)
                    ];
                    $command->partsModification += [
                        $modification->getId() => $modification->getWorkAutosPartsByWorkGroup($linkWorkPartsAuto, $workGroup)
                    ];
                }
            }
        }
        return $command;
    }

    public function getProfit(int $optID)
    {
        return 'profit_' . $optID;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $id = $arr[1] ?: 0;
        return $this->$fieldName[$id] ?? null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $id = $arr[1] ?: 0;
        $this->$fieldName[$id] = $value;
    }
}
