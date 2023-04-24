<?php

namespace App\Widget\Main;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SexWidget extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sex', [$this, 'sex']),
        ];
    }

    public function sex(string $value): string
    {
        switch ($value) {
            case "M": return "Мужской";
            case "F": return "Женский";
        }
        return "";
    }
}
