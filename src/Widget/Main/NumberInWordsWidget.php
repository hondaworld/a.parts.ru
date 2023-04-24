<?php


namespace App\Widget\Main;


use App\Service\Converter\NumberInWordsConverter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NumberInWordsWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('numberInWords', [$this, 'numberInWords'])
        ];
    }

    public function numberInWords(string $text, bool $isRub = false, bool $withKop = true): string
    {
        $numberInWordsConverter = new NumberInWordsConverter();
        return $numberInWordsConverter->getWords($text, $isRub) . ($isRub && $withKop ? ' ' . $numberInWordsConverter->getKop($text) : '');
    }



}