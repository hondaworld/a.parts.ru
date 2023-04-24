<?php

namespace App\Model\Reseller\UseCase\AvitoNotice\Create;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @var int
     */
    public $zapCardID;

    /**
     * @var ZapCard
     */
    public $zapCard;
}
