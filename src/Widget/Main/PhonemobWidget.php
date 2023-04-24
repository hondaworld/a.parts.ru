<?php


namespace App\Widget\Main;


use App\Model\User\Service\PhoneMobileHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PhonemobWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('phone_mobile', [$this, 'phone_mobile'])
        ];
    }

    public function phone_mobile(string $phonemob): string
    {
        return (new PhoneMobileHelper($phonemob))->getValue();
    }
}