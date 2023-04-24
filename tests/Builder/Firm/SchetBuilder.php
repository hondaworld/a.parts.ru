<?php

namespace App\Tests\Builder\Firm;

use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\Schet\Schet;
use App\Tests\Builder\User\UserBuilder;

class SchetBuilder
{
    public function __construct()
    {
    }

    public function build(): Schet
    {
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(true))->build();
        $financeType = new FinanceType('Тест', $firm, false);
        $schet = new Schet($financeType, $user, $firm);

        return $schet;
    }
}