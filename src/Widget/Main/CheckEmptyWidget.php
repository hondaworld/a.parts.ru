<?php


namespace App\Widget\Main;


use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CheckEmptyWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('check_empty', [$this, 'checkEmpty'], ['needs_environment' => true, 'is_safe' => ['html']])
        ];
    }

    public function checkEmpty(Environment $twig, string $text, string $defaultText = 'пусто'): string
    {
        return $twig->render('widget/main/check_empty.html.twig', [
            'text' => $text,
            'defaultText' => $defaultText,
        ]);
    }

}