<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Session;

class WindowCoordsService
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function getTop(): int
    {
        $windowTop = intval($this->session->get('windowTop', 0));
        $this->session->remove('windowTop');
        return $windowTop;
    }

    public function putTop(int $windowTop): void
    {
        $this->session->set('windowTop', $windowTop);
    }
}