<?php

namespace App\Model\Card\UseCase\Location\Perem;


use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var ZapSklad
     */
    public $zapSklad;

    /**
     * @var ZapCard
     */
    public $zapCard;

    /**
     * @var int
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="ID склада должно быть целым числом"
     * )
     * @Assert\Positive()
     */
    public $zapSkladID_to;

    /**
     * @var int
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Количество должно быть целым числом"
     * )
     * @Assert\Positive()
     */
    public $quantity;

    public function __construct(ZapCard $zapCard, ZapSklad $zapSklad)
    {
        $this->zapCard = $zapCard;
        $this->zapSklad = $zapSklad;
    }
}
