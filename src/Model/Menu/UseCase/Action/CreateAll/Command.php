<?php

namespace App\Model\Menu\UseCase\Action\CreateAll;

use App\Model\Menu\Entity\Section\MenuSection;
use App\ReadModel\Menu\MenuActionFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{

    public $section;

    public $actions;

    public $newActions;

    public function __construct(MenuSection $section)
    {
        $this->section = $section;

        $newActions = [];

        foreach (MenuActionFetcher::STANDART_ACTIONS as $standart) {
            $isAbsent = true;
            foreach ($section->getActions() as $existing) {
                if ($existing->isForAction($standart['name'])) {
                    $isAbsent = false;
                }
            }
            if ($isAbsent) $newActions[] = $standart;
        }

        $this->newActions = $newActions;
    }
}
