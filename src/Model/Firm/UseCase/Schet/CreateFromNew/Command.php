<?php

namespace App\Model\Firm\UseCase\Schet\CreateFromNew;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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

    public $finance_typeID;

    public $isEmail;
}
