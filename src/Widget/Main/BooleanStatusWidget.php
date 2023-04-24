<?php


namespace App\Widget\Main;


use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BooleanStatusWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('boolean_status', [$this, 'status'], ['needs_environment' => true, 'is_safe' => ['html']])
        ];
    }

    public function status(Environment $twig, bool $boolean): string
    {
        return $twig->render('widget/main/boolean_status.html.twig', [
            'boolean' => $boolean
        ]);
    }

}