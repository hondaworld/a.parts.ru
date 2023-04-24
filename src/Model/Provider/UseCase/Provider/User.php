<?php


namespace App\Model\Provider\UseCase\Provider;

use Symfony\Component\Validator\Constraints as Assert;

class User
{
    public $id;

    /**
     * @Assert\NotBlank(
     *     message="Клиент должен быть выбран"
     * )
     */
    public $name;

    public function __construct(int $id = 0, string $name = '')
    {
        $this->id = $id;
        $this->name = $name;
    }
}