<?php


namespace App\Widget\Main;


use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BalanceWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('balance', [$this, 'balance'], ['needs_environment' => true, 'is_safe' => ['html']])
        ];
    }

    public function balance(Environment $twig, float $sum, string $addSymbol = ''): string
    {
        return $twig->render('widget/main/balance.html.twig', [
            'sum' => $sum,
            'addSymbol' => $addSymbol
        ]);
    }

}