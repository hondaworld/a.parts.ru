<?php

namespace App\Model\Finance\UseCase\Currency\Edit;

use App\Model\Finance\Entity\Currency\Currency;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $currencyID;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     */
    public $code;

    /**
     * @Assert\NotBlank()
     */
    public $name_short;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $int_name;

    /**
     * @Assert\NotBlank()
     */
    public $int_1;

    /**
     * @Assert\NotBlank()
     */
    public $int_2;

    /**
     * @Assert\NotBlank()
     */
    public $int_5;

    /**
     * @Assert\NotBlank()
     */
    public $fract_name;

    /**
     * @Assert\NotBlank()
     */
    public $fract_1;

    /**
     * @Assert\NotBlank()
     */
    public $fract_2;

    /**
     * @Assert\NotBlank()
     */
    public $fract_5;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $koef;

    /**
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $fix_rate;

    public $is_fix_rate;

    /**
     * @Assert\NotBlank()
     * @Assert\Choice({"A", "M", "F"})
     */
    public $sex;

    public function __construct(int $currencyID)
    {
        $this->currencyID = $currencyID;
    }

    public static function fromCurrency(Currency $currency): self
    {
        $command = new self($currency->getId());
        $command->code = $currency->getCode();
        $command->name_short = $currency->getNameShort();
        $command->name = $currency->getName();
        $command->int_name = $currency->getIntName();
        $command->int_1 = $currency->getInt1();
        $command->int_2 = $currency->getInt2();
        $command->int_5 = $currency->getInt5();
        $command->fract_name = $currency->getFractName();
        $command->fract_1 = $currency->getFract1();
        $command->fract_2 = $currency->getFract2();
        $command->fract_5 = $currency->getFract5();
        $command->sex = $currency->getSex();
        $command->koef = $currency->getKoef();
        $command->fix_rate = $currency->getFixRate();
        $command->is_fix_rate = $currency->getIsFixRate();
        return $command;
    }
}
