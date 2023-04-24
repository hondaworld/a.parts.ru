<?php

namespace App\Tests\Builder\Provider;

use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\User\UserBuilder;

class ProviderBuilder
{
    private string $name;
    private ZapSklad $zapSklad;
    private User $user;
    private int $koef;

    public function __construct(string $name = 'Тестовый прайс-лист')
    {
        $this->user = (new UserBuilder())->build();
        $this->zapSklad = new ZapSklad('Склад', 'Склад', true, '0', null, false);
        $this->name = $name;
    }

    public function build(): Provider
    {
        $provider = new Provider($this->name, $this->user, $this->zapSklad, '0', false);

        return $provider;
    }
}