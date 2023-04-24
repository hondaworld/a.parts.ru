<?php

namespace App\Model\Order\UseCase\Order\Create;

use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Phonemob;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank(
     *     message="Заполните, пожалуйста, имя"
     * )
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $phonemob;

    public function __construct(string $phonemob = '')
    {
        $this->phonemob = (new Phonemob($phonemob))->getValue();
    }

    public static function fromUser(User $user): self
    {
        $command = new self($user->getPhonemob());
        $command->firstname = $user->getUserName()->getFirstname();
        $command->lastname = $user->getUserName()->getLastname();
        return $command;
    }
}
