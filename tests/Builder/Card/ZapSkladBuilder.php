<?php

namespace App\Tests\Builder\Card;


use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;

class ZapSkladBuilder
{
    private float $koef = 0;
    private ?Opt $opt;

    public function __construct($koef = 0, ?Opt $opt = null)
    {
        $this->koef = $koef;
        $this->opt = $opt;
    }

    public function build(): ZapSklad
    {
        $zapSklad = new ZapSklad('Склад', 'Склад', true, $this->koef, $this->opt, false);

        return $zapSklad;
    }
}