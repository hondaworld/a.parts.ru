<?php


namespace App\Service\Detail;


class DetailNumberConverter
{
    public function convert(string $value): string
    {
        return strtoupper(preg_replace("/[\-\.\ \'\"\=]/", "", trim($value)));
    }
}