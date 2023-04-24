<?php

namespace App\Model\Firm\UseCase\Firm\Others;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $firmID;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Regex (
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     *  @Assert\Positive
     */

    public $first_schet;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Regex (
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     *  @Assert\Positive
     */

    public $first_nakladnaya;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Regex (
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     *  @Assert\Positive
     */

    public $first_schetfak;

    public function __construct(int $firmID)
    {
        $this->firmID = $firmID;
    }

    public static function fromEntity(Firm $firm): self
    {
        $command = new self($firm->getId());
        $command->first_schet = $firm->getFirstSchet();
        $command->first_nakladnaya = $firm->getFirstNakladnaya();
        $command->first_schetfak = $firm->getFirstSchetfak();
        return $command;
    }
}
