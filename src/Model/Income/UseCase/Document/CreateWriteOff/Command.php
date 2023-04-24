<?php

namespace App\Model\Income\UseCase\Document\CreateWriteOff;

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

    public $returning_reason;

    /**
     * @var array
     */
    public $incomeSklads;

    public function __construct(array $incomeSklads)
    {
        $this->incomeSklads = $incomeSklads;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $incomeSkladID = $arr[1];
        return $this->incomeSklads[$incomeSkladID] ?? null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $fieldName = $arr[0];
        $incomeSkladID = $arr[1];
        $this->incomeSklads[$incomeSkladID] = $value;
    }
}
