<?php

namespace App\Service\Price;

class BalancePercent
{
    public function get(float $now, float $prev): float
    {
        if ($now == 0) return 0;
        return round(($now - $prev) / $now * 100, 2);
    }
}