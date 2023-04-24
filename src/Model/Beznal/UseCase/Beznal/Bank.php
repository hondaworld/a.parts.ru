<?php


namespace App\Model\Beznal\UseCase\Beznal;

use Symfony\Component\Validator\Constraints as Assert;

class Bank
{
    public $id;

    /**
     * @Assert\NotBlank(
     *     message="Банк должен быть выбран"
     * )
     */
    public $name;

    public function __construct(int $id = 0, string $name = '')
    {
        $this->id = $id;
        $this->name = $name;
    }
}