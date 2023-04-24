<?php

namespace App\Model\User\Service;

class PhoneMobileHelper
{
    private string $phonemob;

    public function __construct(string $phonemob)
    {
        $this->phonemob = $phonemob;
    }

    public function getValue(): string
    {
        if (strpos($this->phonemob, '+7') === 0)
            return vsprintf("%s%d (%d%d%d) %d%d%d-%d%d-%d%d", str_split($this->phonemob));
        if (strpos($this->phonemob, '+380') === 0)
            return vsprintf("%s%d%d%d (%d%d%d) %d%d%d-%d%d-%d%d", str_split($this->phonemob));
        if (strpos($this->phonemob, '+375') === 0)
            return vsprintf("%s%d%d%d (%d%d) %d%d%d-%d%d-%d%d", str_split($this->phonemob));

        return $this->phonemob;
    }
}