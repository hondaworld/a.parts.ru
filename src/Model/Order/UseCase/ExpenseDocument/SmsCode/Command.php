<?php

namespace App\Model\Order\UseCase\ExpenseDocument\SmsCode;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $sms_code;
}
