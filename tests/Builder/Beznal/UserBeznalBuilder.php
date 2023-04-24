<?php

namespace App\Tests\Builder\Beznal;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\User\Entity\User\User;

class UserBeznalBuilder
{
    private bool $isMain;
    private Bank $bank;
    private User $user;

    public function __construct(User $user, bool $isMain = false)
    {
        $this->isMain = $isMain;
        $this->user = $user;
        $this->bank = new Bank('123456', 'Банк', '1245', 'Москва, 1', 'Описание');
    }

    public function build(): Beznal
    {
        $beznal = new Beznal($this->user, $this->bank, '1234567849499', 'Описание', $this->isMain);
        $this->user->assignBeznal($beznal);

        return $beznal;
    }
}