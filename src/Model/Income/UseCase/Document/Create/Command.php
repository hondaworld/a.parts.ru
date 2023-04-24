<?php

namespace App\Model\Income\UseCase\Document\Create;

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

    /**
     * @Assert\NotBlank(
     *     message="Выберите, пожалйуста, адрес поставщика"
     * )
     */
    public $user_contactID;

    public $osn_nakladnaya;

    public $osn_schet;

    public $is_priceZak;

    /**
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Оплата поставщику должно быть дробным числом"
     * )
     */
    public $balance;

    /**
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="НДС оплаты поставщику должно быть дробным числом"
     * )
     */
    public $balance_nds;

    public $description;

    public function __construct(FirmFetcher $firmFetcher)
    {
        $this->is_priceZak = true;
        $this->firmID = $firmFetcher->getMainFirmID() ?: null;
    }

    public function normalizeNumber(string $num): float
    {
        return floatval(str_replace(',', '.', $num));
    }
}
