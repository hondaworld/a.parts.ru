<?php

namespace App\Model\Firm\UseCase\Firm\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="50",
     *     minMessage="Краткое наименование должно быть меньше 50 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     * @Assert\Length(
     *     min="10",
     *     max="12",
     *     minMessage="ИНН должен содержать 10 или 12 цифр",
     *     maxMessage="ИНН должен содержать 10 или 12 цифр"
     * )
     */

    public $inn;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     * @Assert\Length(
     *     min="9",
     *     max="9",
     *     exactMessage="КПП должен содержать 9 цифр"
     * )
     */
    public $kpp;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     * @Assert\Length(
     *     min="8",
     *     max="8",
     *     exactMessage="ОКПО должен содержать 8 цифр"
     * )
     */
    public $okpo;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     * @Assert\Length(
     *     max="15",
     *     maxMessage="ОГРН должен содержать максимум 15 цифр"
     * )
     */
    public $ogrn;

    /**
     * @var bool
     */
    public $isNDS;

    /**
     * @var bool
     */
    public $isUr;

    /**
     * @var int
     */
    public $nalogID;

    /**
     * @var int
     */
    public $directorID;

    /**
     * @var int
     */
    public $buhgalterID;

}
