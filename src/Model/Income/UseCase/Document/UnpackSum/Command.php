<?php

namespace App\Model\Income\UseCase\Document\UnpackSum;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank ()
     */
    public $sum;

    public function normalizeSum(): float
    {
        return floatval(str_replace(',', '.', $this->sum));
    }

    public function sumIsEqual(float $sum): bool
    {
        return $this->normalizeSum() == $sum;
    }
}
