<?php

namespace App\Tests\Builder\Order;

use App\Model\Order\Entity\Order\Order;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\User\UserBuilder;

class OrderBuilder
{
    private User $user;

    public function __construct(?User $user = null)
    {
        if ($user) {
            $this->user = $user;
        } else {
            $this->user = (new UserBuilder())->build();
        }
    }

    public function build(): Order
    {
        $order = new Order(
            $this->user,
            null,
            null
        );

        return $order;
    }
}