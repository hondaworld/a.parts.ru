<?php

namespace App\Model\Expense\UseCase\SchetFakKor\Create;

use App\Model\Firm\Entity\Schet\Schet;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank(
     *     message="Выберите счет-фактуру"
     * )
     */
    public $schet_fakID;

    /**
     * @Assert\Length(
     *     max="15",
     *     minMessage="Префикс должен быть не больше 15 символов"
     * )
     */
    public $document_prefix;

    /**
     * @Assert\Length(
     *     max="15",
     *     minMessage="Суфикс должен быть не больше 15 символов"
     * )
     */
    public $document_sufix;
}
