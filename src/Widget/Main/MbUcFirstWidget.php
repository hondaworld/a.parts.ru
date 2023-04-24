<?php


namespace App\Widget\Main;


use App\Service\Converter\NumberInWordsConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MbUcFirstWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('mb_ucfirst', [$this, 'mbUcFirst'])
        ];
    }

    public function mbUcFirst(string $text): string
    {
        $text = trim($text);
        return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
    }



}