<?php

namespace App\ReadModel\Card;

class ZapCardStockView
{
    public $stockID;
    public $name;
    public $text;
    public $price_stock;

    public function isStock(): bool
    {
        return $this->stockID != null;
    }

    public function getPrice(): float
    {
        return $this->price_stock ?: 0;
    }

    public function hasPrice(): bool
    {
        return $this->getPrice() > 0;
    }
}