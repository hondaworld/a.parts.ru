<?php

namespace App\Model\Shop\UseCase\PayMethod\Edit;

use App\Model\Shop\Entity\PayMethod\PayMethod;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $payMethodID;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="150",
     *     exactMessage="Наименование должно быть не больше 150 символов"
     * )
     */
    public $val;

    public $description;

    public $isMain;

    public function __construct(int $payMethodID)
    {
        $this->payMethodID = $payMethodID;
    }

    public static function fromEntity(PayMethod $payMethod): self
    {
        $command = new self($payMethod->getId());
        $command->val = $payMethod->getVal();
        $command->description = $payMethod->getDescription();
        $command->isMain = $payMethod->isMain();
        return $command;
    }
}
