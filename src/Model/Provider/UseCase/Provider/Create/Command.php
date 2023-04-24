<?php

namespace App\Model\Provider\UseCase\Provider\Create;

use App\Model\Provider\UseCase\Provider\User;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $koef_dealer;

    /**
     * @var boolean
     */
    public $isDealer;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $zapSkladID;

    /**
     * @var int
     * @Assert\Valid()
     */
    public $user;

    public function __construct(ZapSkladRepository $zapSkladRepository)
    {
        $this->zapSkladID = $zapSkladRepository->getMain()->getId();
        $this->user = new User();
    }
}
