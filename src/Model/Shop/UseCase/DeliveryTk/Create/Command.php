<?php

namespace App\Model\Shop\UseCase\DeliveryTk\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $http;

    /**
     * @var string
     * @Assert\Length(
     *     max="255",
     *     exactMessage="SMS сообщение должно быть не больше 255 символов"
     * )
     */
    public $sms_text;
}
