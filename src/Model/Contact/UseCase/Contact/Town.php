<?php


namespace App\Model\Contact\UseCase\Contact;

use Symfony\Component\Validator\Constraints as Assert;

class Town
{
    public $id;

    /**
     * @Assert\NotBlank(
     *     message="Город должен быть выбран"
     * )
     */
    public $name;

    public function __construct(int $id = 0, string $name = '')
    {
        $this->id = $id;
        $this->name = $name;
    }
}