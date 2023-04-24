<?php

namespace App\Model\Income\UseCase\Document\CreateReturn;

use App\Model\Income\Entity\Income\Income;
use App\ReadModel\Firm\FirmFetcher;
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
     *     message="Выберите, пожалйуста, организацию"
     * )
     */
    public $firmID;

    /**
     * @Assert\NotBlank(
     *     message="Выберите, пожалйуста, поставщика"
     * )
     */
    public $providerID;

    public $returning_reason;

    /**
     * @var array
     */
    public $incomeSklads;

    public function __construct(FirmFetcher $firmFetcher, array $incomeSklads)
    {
        $this->firmID = $firmFetcher->getMainFirmID() ?: null;
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
