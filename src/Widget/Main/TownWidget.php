<?php


namespace App\Widget\Main;


use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TownWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('town', [$this, 'town'])
        ];
    }

    public function town(?string $town, ?string $region, string $country = null): string
    {
        $result = '';
        if ($country) $result .= $country . ', ';
        if ($region != $town) $result .= $region . ' - ';
        if ($town) $result .= $town;
        return $result;
    }
}