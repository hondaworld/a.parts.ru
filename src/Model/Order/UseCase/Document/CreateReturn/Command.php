<?php

namespace App\Model\Order\UseCase\Document\CreateReturn;

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

    /**
     * @Assert\NotBlank(
     *     message="Выберите, пожалйуста, склад"
     * )
     */
    public $zapSkladD;

    public $returning_reason;

    /**
     * @var array
     */
    public $goods;

    public function __construct(array $goods)
    {
        $this->goods = $goods;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $goodID = $arr[1];
        return $this->goods[$goodID] ?? null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $goodID = $arr[1];
        $this->goods[$goodID] = $value;
    }
}
