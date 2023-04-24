<?php

namespace App\Model\Provider\UseCase\Provider\Edit;

use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\UseCase\Provider\User;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerID;
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
     * @var User
     * @Assert\Valid()
     */
    public $user;

    public function __construct(int $providerID)
    {
        $this->providerID = $providerID;
    }

    public static function fromEntity(Provider $provider): self
    {
        $command = new self($provider->getId());
        $command->name = $provider->getName();
        $command->koef_dealer = $provider->getKoefDealer();
        $command->isDealer = $provider->isDealer();
        $command->zapSkladID = $provider->getZapSklad()->getId();
        $command->user = new User($provider->getUser()->getId(), $provider->getUser()->getFullNameWithPhoneMobileAndOrganization());
        return $command;
    }
}
