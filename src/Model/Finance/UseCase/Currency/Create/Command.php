<?php

namespace App\Model\Finance\UseCase\Currency\Create;

use App\Model\Finance\Entity\Currency\Currency;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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
}
