<?php


namespace App\Model\Manager\UseCase\Profile;

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

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}