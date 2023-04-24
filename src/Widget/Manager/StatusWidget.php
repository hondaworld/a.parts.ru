<?php


namespace App\Widget\Manager;


use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StatusWidget extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('manager_status', [$this, 'status'], ['needs_environment' => true, 'is_safe' => ['html']])
        ];
    }

    public function status(Environment $twig, bool $isManager, bool $isAdmin): string
    {
        return $twig->render('widget/manager/status.html.twig', [
            'isManager' => $isManager,
            'isAdmin' => $isAdmin
        ]);
    }
}